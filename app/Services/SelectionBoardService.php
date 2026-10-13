<?php

namespace App\Services;

use App\Enums\SelectionBoardStatus;
use App\Models\SelectionBoard;
use App\Models\SelectionOption;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Owns the SelectionBoard aggregate: the board itself, its candidate
 * options, and the one decision eventually recorded against it. Unlike
 * DesignService there's no revision history to manage — a board is a
 * single pick among fixed alternatives, not an iterative review cycle —
 * so the lifecycle is a flat Draft -> Sent -> Decided (or Cancelled at
 * any point before Decided).
 */
class SelectionBoardService
{
    public function __construct(
        protected NumberingService $numbering,
        protected FileService $files,
    ) {}

    public function create(array $data, ?User $createdBy = null): SelectionBoard
    {
        return DB::transaction(function () use ($data, $createdBy) {
            $data['board_no'] = $this->numbering->next('SEL');
            $data['created_by'] = $createdBy?->id;
            $data['status'] = SelectionBoardStatus::Draft->value;

            return SelectionBoard::create($data);
        });
    }

    public function update(SelectionBoard $board, array $data): SelectionBoard
    {
        if ($board->isLocked()) {
            throw ValidationException::withMessages([
                'board' => 'This selection has already been decided or cancelled and can no longer be edited.',
            ]);
        }

        $board->update($data);

        return $board->fresh();
    }

    /**
     * Restricted to Draft, not just "not locked" — once a board has been
     * sent, the client is choosing from a fixed set; silently changing
     * what's on offer mid-decision would be worse than just not allowing
     * it. Withdraw it with cancel() and start a fresh board instead.
     */
    public function addOption(SelectionBoard $board, array $data, ?UploadedFile $image = null): SelectionOption
    {
        if ($board->status !== SelectionBoardStatus::Draft) {
            throw ValidationException::withMessages([
                'option' => 'Options can only be added while this selection is still a draft.',
            ]);
        }

        return DB::transaction(function () use ($board, $data, $image) {
            if ($image) {
                // FileService::store() needs a model with the `documents`
                // morphMany to attach through — the board has it
                // (HasDocuments), the option doesn't, same split as
                // Design/DesignRevision.
                $document = $this->files->store($image, $board, 'selection_option');
                $data['file_document_id'] = $document->id;
            }

            $data['selection_board_id'] = $board->id;

            return SelectionOption::create($data);
        });
    }

    public function removeOption(SelectionBoard $board, SelectionOption $option): void
    {
        if ($board->status !== SelectionBoardStatus::Draft) {
            throw ValidationException::withMessages([
                'option' => 'Options can only be removed while this selection is still a draft.',
            ]);
        }

        if ($option->selection_board_id !== $board->id) {
            throw ValidationException::withMessages([
                'option' => 'That option does not belong to this selection.',
            ]);
        }

        DB::transaction(function () use ($option) {
            if ($option->file) {
                $this->files->delete($option->file);
            }

            $option->delete();
        });
    }

    /**
     * Shares the board with the client. Requires at least two options —
     * a "choice" of one isn't a choice, and catching this here means the
     * client portal never shows a half-built board.
     */
    public function send(SelectionBoard $board): SelectionBoard
    {
        if ($board->status !== SelectionBoardStatus::Draft) {
            throw ValidationException::withMessages([
                'board' => 'Only a draft selection can be sent to the client.',
            ]);
        }

        if ($board->options()->count() < 2) {
            throw ValidationException::withMessages([
                'board' => 'Add at least two options before sending this selection to the client.',
            ]);
        }

        $board->update(['status' => SelectionBoardStatus::Sent->value]);

        return $board->fresh();
    }

    public function cancel(SelectionBoard $board): SelectionBoard
    {
        if ($board->status === SelectionBoardStatus::Decided) {
            throw ValidationException::withMessages([
                'board' => 'A decided selection cannot be cancelled.',
            ]);
        }

        $board->update(['status' => SelectionBoardStatus::Cancelled->value]);

        return $board->fresh();
    }

    /**
     * Records the client's pick. Called identically from the client
     * portal (the client's own decision) and from the staff Show page
     * (staff recording it on the client's behalf, gated on the distinct
     * `decide_material_selection` ability) — same dual-caller pattern as
     * DesignService::recordClientApproval().
     */
    public function recordDecision(
        SelectionBoard $board,
        SelectionOption $option,
        ?string $comment = null,
    ): SelectionBoard {
        if ($board->status !== SelectionBoardStatus::Sent) {
            throw ValidationException::withMessages([
                'board' => 'This selection has not been sent to the client yet.',
            ]);
        }

        if ($option->selection_board_id !== $board->id) {
            throw ValidationException::withMessages([
                'option' => 'That option does not belong to this selection.',
            ]);
        }

        return DB::transaction(function () use ($board, $option, $comment) {
            $board->update([
                'decided_option_id' => $option->id,
                'decided_at' => now(),
                'client_comment' => $comment,
                'status' => SelectionBoardStatus::Decided->value,
            ]);

            return $board->fresh();
        });
    }
}
