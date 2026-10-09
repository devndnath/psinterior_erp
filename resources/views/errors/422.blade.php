@include('errors.minimal', [
    'code' => 422,
    'heading' => 'Invalid request',
    'message' => 'The data submitted could not be processed. Please check the form and try again.',
])
