<?php

namespace App\Http\Requests\Bordereauremise;

use App\Http\Requests\SearchRequest;
use App\Http\Requests\ISearchFormRequest;

use Illuminate\Foundation\Http\FormRequest;

class FetchRequest extends FormRequest  implements ISearchFormRequest
{
    use SearchRequest;

    /**
     * @inheritDoc
     */
    protected function orderByFields(): array
    {
        return ['numero_transaction', 'localisation'];
    }

    /**
     * @inheritDoc
     */
    protected function defaultOrderByField(): string
    {
        return 'numero_transaction';
    }

    protected function getCustomPayload()
    {
        $payload = "";
        //$payload = $this->addToPayload($payload, 'search', $this->search);
        $payload = $this->addToPayload($payload, 'dateremise_du', substr($this->dateremise_du, 0, 10));
        $payload = $this->addToPayload($payload, 'dateremise_au', substr($this->dateremise_au, 0, 10));
        $payload = $this->addToPayload($payload, 'localisation', $this->localisation);
        $payload = $this->addToPayload($payload, 'type', $this->type);
        $payload = $this->addToPayload($payload, 'statut', $this->statut);

        return $payload;
    }
}
