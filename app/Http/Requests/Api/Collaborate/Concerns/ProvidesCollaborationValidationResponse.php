<?php

namespace App\Http\Requests\Api\Collaborate\Concerns;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\MessageBag;

trait ProvidesCollaborationValidationResponse
{
    abstract protected function collaborationValidationSummary(): string;

    /**
     * Remap validation keys in the API response (e.g. company_name → name for the frontend).
     *
     * @return array<string, string>
     */
    protected function collaborationErrorKeyAliases(): array
    {
        return [];
    }

    protected function failedValidation(Validator $validator): void
    {
        $errors = $this->applyCollaborationErrorKeyAliases($validator->errors());

        throw new HttpResponseException(response()->json([
            'message' => $errors->first() ?: $this->collaborationValidationSummary(),
            'errors' => $errors,
        ], 422));
    }

    protected function applyCollaborationErrorKeyAliases(MessageBag $errors): MessageBag
    {
        foreach ($this->collaborationErrorKeyAliases() as $from => $to) {
            if ($errors->has($from)) {
                $errors->merge([$to => $errors->get($from)]);
                $errors->forget($from);
            }
        }

        return $errors;
    }
}
