@include('errors.minimal', [
    'code' => 403,
    'heading' => 'Access denied',
    'message' => "You don't have permission to view this page. Contact an administrator if you believe this is a mistake.",
])
