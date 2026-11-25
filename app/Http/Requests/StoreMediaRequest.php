<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMediaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normalize input before validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('type') && is_string($this->input('type'))) {
            $this->merge([
                'type' => strtolower(trim($this->input('type'))),
            ]);
        }

        if ($this->has('title') && is_string($this->input('title'))) {
            $this->merge([
                'title' => trim($this->input('title')),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $isPatch = $this->isMethod('PATCH');
        $media = $this->route('media');
        $effectiveType = $this->input('type') ?? $media?->type;
        $needsCountry = $effectiveType === 'drama' && (!$isPatch || ($this->has('type') && empty($media?->country)));

        return [
            'title' => [
                $isPatch ? 'sometimes' : 'required',
                'required',
                'string',
                'max:255',
                Rule::unique('media', 'title')->ignore($media),
            ],
            'type' => [
                $isPatch ? 'sometimes' : 'required',
                'required',
                'string',
                Rule::in(['anime', 'drama']),
            ],
            'genre' => [
                $isPatch ? 'sometimes' : 'required',
                'required',
                'string',
                'max:100',
            ],
            'year' => [
                $isPatch ? 'sometimes' : 'required',
                'required',
                'integer',
                'min:1900',
                'max:' . (date('Y') + 5),
            ],
            'episodes' => [
                $isPatch ? 'sometimes' : 'required',
                'required',
                'integer',
                'min:1',
            ],
            'country' => [
                $needsCountry ? 'required' : ($isPatch ? 'sometimes' : 'nullable'),
                'nullable',
                'string',
                'max:100',
            ],
        ];
    }

    /**
     * Get custom validation error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Judul media wajib diisi',
            'title.unique' => 'Judul media sudah terdaftar',
            'title.max' => 'Judul media maksimal 255 karakter',
            'type.required' => 'Jenis media wajib diisi',
            'type.in' => 'Jenis media harus bernilai anime atau drama',
            'genre.required' => 'Genre media wajib diisi',
            'year.required' => 'Tahun rilis wajib diisi',
            'year.integer' => 'Tahun rilis harus berupa angka',
            'year.min' => 'Tahun rilis minimal 1900',
            'episodes.required' => 'Jumlah episode wajib diisi',
            'episodes.integer' => 'Jumlah episode harus berupa angka',
            'episodes.min' => 'Jumlah episode minimal 1',
            'country.required' => 'Negara asal wajib diisi untuk media jenis drama',
        ];
    }
}
