<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\HttpMethod;
use App\Models\Monitor;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMonitorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Monitor::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'url_address' => [
                'required_without:ip_address',
                'nullable',
                'prohibits:ip_address',
                'string',
                'max:255',
                'url:http,https',
            ],
            'ip_address' => [
                'required_without:url_address',
                'nullable',
                'prohibits:url_address',
                'ip',
            ],
            'request_method' => ['required', Rule::enum(HttpMethod::class)],
            'request_headers' => ['nullable', 'array'],
            'request_headers.*' => ['string'],
            'is_httpable' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $headers = $this->input('request_headers');

        if (is_string($headers)) {
            if ($headers === '') {
                $this->merge(['request_headers' => null]);
            } else {
                $decoded = json_decode($headers, true);

                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $this->merge(['request_headers' => $decoded]);
                }
            }
        }

        $this->merge([
            'url_address' => $this->filled('url_address') ? $this->input('url_address') : null,
            'ip_address' => $this->filled('ip_address') ? $this->input('ip_address') : null,
        ]);
    }
}
