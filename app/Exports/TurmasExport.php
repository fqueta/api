<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TurmasExport implements FromCollection, WithHeadings, WithMapping
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection(): Collection
    {
        $q = DB::table('turmas')
            ->leftJoin('cursos', 'turmas.id_curso', '=', 'cursos.id')
            ->select(
                'turmas.*',
                'cursos.nome as nome_curso',
                'cursos.titulo as titulo_curso',
            )
            ->where('turmas.excluido', 'n')
            ->where('turmas.deletado', 'n');

        if (!empty($this->filters['id_curso'])) {
            $val = $this->filters['id_curso'];
            if (is_string($val) && str_contains($val, ',')) {
                $q->whereIn('turmas.id_curso', explode(',', $val));
            } else {
                $q->where('turmas.id_curso', $val);
            }
        }
        if (!empty($this->filters['id'])) {
            $q->where('turmas.id', $this->filters['id']);
        }
        if (!empty($this->filters['status'])) {
            $q->where('turmas.status', $this->filters['status']);
        }
        if (!empty($this->filters['ativo'])) {
            $q->where('turmas.ativo', $this->filters['ativo']);
        }

        $q->orderBy('turmas.id', 'asc');

        return $q->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nome',
            'ID Curso',
            'Curso',
            'Titulo Curso',
            'Inicio',
            'Fim',
            'Max Alunos',
            'Ativo',
            'Token',
            'Data Criacao',
            'Data Atualizacao',
        ];
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->nome,
            $row->id_curso,
            $row->nome_curso,
            $row->titulo_curso,
            $row->inicio,
            $row->fim,
            $row->max_alunos,
            $row->ativo,
            $row->token,
            $row->data ?? $row->created_at,
            $row->atualizado ?? $row->updated_at,
        ];
    }
}
