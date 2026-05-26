<?php

namespace App\Http\Requests\SupportTickets;

use App\Models\SupportTicket;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSupportTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var SupportTicket $ticket */
        $ticket = $this->route('support_ticket');

        return $this->user()->can('update', $ticket);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $canManage = $this->user()->can('support-tickets.manage');

        $rules = [
            'subject' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'required', 'string', 'max:20000'],
            'type' => ['sometimes', 'required', 'in:delivery_issue,billing,account,complaint,general'],
            'priority' => ['sometimes', 'required', 'in:low,medium,high,urgent'],
            'parcel_id' => ['nullable', 'exists:parcels,id'],
            'attachments' => ['nullable', 'array', 'max:10'],
            'attachments.*' => ['file', 'max:10240', 'mimes:jpeg,jpg,png,gif,webp,pdf,txt,doc,docx'],
        ];

        if ($canManage) {
            $rules['status'] = [
                'sometimes',
                'required',
                Rule::in(['open', 'pending', 'in_progress', 'resolved', 'closed']),
            ];
            $rules['assigned_to'] = ['nullable', 'exists:users,id'];
        }

        return $rules;
    }

    #[\Override]
    public function validated($key = null, $default = null): mixed
    {
        /** @var array<string, mixed> $data */
        $data = parent::validated();

        if (! $this->user()->can('support-tickets.manage')) {
            unset($data['status'], $data['assigned_to']);
        }

        if ($key !== null) {
            return data_get($data, $key, $default);
        }

        return $data;
    }
}
