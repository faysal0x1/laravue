<?php

namespace App\Http\Requests\SupportTickets;

use App\Models\SupportTicket;
use Illuminate\Foundation\Http\FormRequest;

class StoreSupportTicketReplyRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var SupportTicket $ticket */
        $ticket = $this->route('support_ticket');

        return $this->user()->can('reply', $ticket);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $canInternal = $this->user()->can('support-tickets.manage');

        return [
            'message' => ['required', 'string', 'max:20000'],
            'is_internal' => $canInternal ? ['sometimes', 'boolean'] : ['prohibited'],
            'attachments' => ['nullable', 'array', 'max:10'],
            'attachments.*' => ['file', 'max:10240', 'mimes:jpeg,jpg,png,gif,webp,pdf,txt,doc,docx'],
        ];
    }

    #[\Override]
    protected function prepareForValidation(): void
    {
        if ($this->has('is_internal')) {
            $this->merge([
                'is_internal' => filter_var($this->input('is_internal'), FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }
}
