<?php

namespace App\Services;

use App\Enums\ApprovalDecision;
use App\Enums\DesignRevisionStatus;
use App\Enums\DesignStatus;
use App\Models\Design;
use App\Models\DesignRevision;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * Owns the Design aggregate: the design record, its append-only revision
 * history, and the client decisions recorded against each revision.
 * Revisions are never overwritten or renumbered (ARCHITECTURE.md §3.5 /
 * spec §13) — every upload is a brand-new row with the next version
 * number, and `design_revisions.status` is the only thing that ever
 * changes on an existing revision.
 */
class DesignService
{
    public function __construct(
        protected NumberingService $numbering,
        protected FileService $files,
    ) {}

    public function create(array $data, ?UploadedFile $file = null, ?User $uploadedBy = null): Design
    {
        return DB::transaction(function () use ($data, $file, $uploadedBy) {
            $data['design_no'] = $this->numbering->next('DSG');
            $design = Design::create($data);

            if ($file) {
                $this->addRevision($design, $file, null, $uploadedBy);
            }

            return $design->fresh();
        });
    }

    public function update(Design $design, array $data): Design
    {
        $design->update($data);

        return $design->fresh();
    }

    /**
     * Uploads the next version for a design. The first revision also
     * bumps the design out of Draft into Submitted — uploading something
     * for review is what "submitted" means here.
     */
    public function addRevision(
        Design $design,
        UploadedFile $file,
        ?string $description = null,
        ?User $uploadedBy = null,
        ?string $drawingNo = null,
        ?string $issueDate = null,
    ): DesignRevision {
        return DB::transaction(function () use ($design, $file, $description, $uploadedBy, $drawingNo, $issueDate) {
            $document = $this->files->store($file, $design, 'design_revision');

            $nextVersion = (int) $design->revisions()->max('version') + 1;

            $revision = $design->revisions()->create([
                'version' => $nextVersion,
                'drawing_no' => $drawingNo,
                // Defaults to today — every revision is issued on the day
                // it's uploaded unless told otherwise, so the drawing
                // register never has a blank issue date to chase down.
                'issue_date' => $issueDate ?: now()->toDateString(),
                'file_document_id' => $document->id,
                'uploaded_by' => $uploadedBy?->id,
                'description' => $description,
                'status' => DesignRevisionStatus::Submitted->value,
            ]);

            if ($design->status === DesignStatus::Draft) {
                $design->update(['status' => DesignStatus::Submitted->value]);
            }

            return $revision;
        });
    }

    /**
     * Records a client decision against a specific revision. Until the
     * client portal exists (spec §43, deferred to a later phase), staff
     * record this on the client's behalf — the row still captures who the
     * decision belongs to (`client_id`) and when, exactly as it will once
     * the portal submits it directly.
     */
    public function recordClientApproval(
        DesignRevision $revision,
        string $decision,
        ?string $comment = null,
        ?string $ipAddress = null,
    ): DesignRevision {
        return DB::transaction(function () use ($revision, $decision, $comment, $ipAddress) {
            $design = $revision->design;

            $revision->approvals()->create([
                'client_id' => $design->project->client_id,
                'decision' => $decision,
                'comment' => $comment,
                'ip_address' => $ipAddress,
                'decided_at' => now(),
            ]);

            $revisionStatus = match ($decision) {
                ApprovalDecision::Approved->value => DesignRevisionStatus::Approved,
                ApprovalDecision::Rejected->value => DesignRevisionStatus::Rejected,
                default => DesignRevisionStatus::RevisionRequired,
            };

            $revision->update([
                'status' => $revisionStatus->value,
                'client_feedback' => $comment,
            ]);

            $design->update([
                'client_approval_status' => $decision,
                'status' => match ($decision) {
                    ApprovalDecision::Approved->value => DesignStatus::Approved->value,
                    ApprovalDecision::Rejected->value => DesignStatus::Rejected->value,
                    // "Revision requested" leaves the design Under Review —
                    // staff upload a new revision next, which is still part
                    // of the same review cycle, not a fresh submission.
                    default => DesignStatus::UnderReview->value,
                },
            ]);

            return $revision->fresh();
        });
    }
}
