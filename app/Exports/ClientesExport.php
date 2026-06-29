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
            ->where('clientes.excluido', 'n')
            ->where('clientes.deletado', 'n');

        if (!empty($this->filters['id'])) {
            $q->where('clientes.id', $this->filters['id']);
        }
        if (!empty($this->filters['status'])) {
            $q->where('clientes.status', $this->filters['status']);
        }
        if (!empty($this->filters['search'])) {
            $s = $this->filters['search'];
            $q->where(function ($qq) use ($s) {
                $qq->where('clientes.Nome', 'like', "%{$s}%")
                   ->orWhere('clientes.sobrenome', 'like', "%{$s}%")
                   ->orWhere('clientes.Email', 'like', "%{$s}%")
                   ->orWhere('clientes.Cpf', 'like', "%{$s}%")
                   ->orWhere('clientes.telefonezap', 'like', "%{$s}%");
            });
        }
        if (!empty($this->filters['id_curso']) || !empty($this->filters['status_matricula'])) {
            $q->whereExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('matriculas')
                      ->whereColumn('matriculas.id_cliente', 'clientes.id')
                      ->where('matriculas.excluido', 'n')
                      ->where('matriculas.deletado', 'n');

                if (!empty($this->filters['id_curso'])) {
                    $val = $this->filters['id_curso'];
                    if (is_string($val) && str_contains($val, ',')) {
                        $query->whereIn('matriculas.id_curso', explode(',', $val));
                    } else {
                        $query->where('matriculas.id_curso', $val);
                    }
                }
                if (!empty($this->filters['status_matricula'])) {
                    $val = $this->filters['status_matricula'];
                    if (is_string($val) && str_contains($val, ',')) {
                        $query->whereIn('matriculas.status', explode(',', $val));
                    } else {
                        $query->where('matriculas.status', $val);
                    }
                }
            });
        }

        $q->orderBy('clientes.id', 'asc');

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
