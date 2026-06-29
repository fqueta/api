<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ClientesExport implements FromCollection, WithHeadings, WithMapping
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection(): Collection
    {
        $q = DB::table('clientes')
            ->where('excluido', 'n')
            ->where('deletado', 'n');

        if (!empty($this->filters['id'])) {
            $q->where('id', $this->filters['id']);
        }
        if (!empty($this->filters['status'])) {
            $q->where('status', $this->filters['status']);
        }
        if (!empty($this->filters['search'])) {
            $s = $this->filters['search'];
            $q->where(function ($qq) use ($s) {
                $qq->where('Nome', 'like', "%{$s}%")
                   ->orWhere('sobrenome', 'like', "%{$s}%")
                   ->orWhere('Email', 'like', "%{$s}%")
                   ->orWhere('Cpf', 'like', "%{$s}%")
                   ->orWhere('telefonezap', 'like', "%{$s}%");
            });
        }

        $q->orderBy('id', 'asc');

        return $q->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nome',
            'Sobrenome',
            'Email',
            'Telefone',
            'Telefone Zap',
            'CPF',
            'RG',
            'Data Nascimento',
            'Sexo',
            'Nacionalidade',
            'Endereco',
            'Numero',
            'Complemento',
            'Bairro',
            'Cidade',
            'UF',
            'CEP',
            'Profissao',
            'Estado Civil',
            'Token',
            'Ativo',
            'Data Cadastro',
            'Data Atualizacao',
        ];
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->Nome,
            $row->sobrenome,
            $row->Email,
            $row->Tel,
            $row->telefonezap,
            $row->Cpf,
            $row->Ident,
            $row->DtNasc2,
            $row->sexo,
            $row->nacionalidade,
            $row->Endereco,
            $row->Numero,
            $row->Compl,
            $row->Bairro,
            $row->Cidade,
            $row->Uf,
            $row->Cep,
            $row->profissao,
            $row->estado_civil,
            $row->token,
            $row->ativo,
            $row->data,
            $row->atualizado,
        ];
    }
}
