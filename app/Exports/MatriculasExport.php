<?php

namespace App\Exports;

use App\Models\Matricula;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class MatriculasExport implements FromCollection, WithHeadings, WithMapping
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection(): Collection
    {
        $q = Matricula::select(
            'matriculas.*',
            'clientes.Nome',
            'clientes.sobrenome',
            'clientes.telefonezap',
            'clientes.Tel',
            'clientes.Email',
            'clientes.Cpf as cpf_aluno',
            'clientes.nacionalidade',
            'clientes.Endereco',
            'clientes.Numero',
            'clientes.Bairro',
            'clientes.Cidade',
            'clientes.Uf',
            'clientes.Cep As cep',
            'clientes.Compl',
            'clientes.Ident As identidade',
            'clientes.DtNasc2 As data_nascimento',
            'clientes.estado_civil',
            'clientes.profissao',
            'cursos.tipo as tipo_curso',
            'cursos.nome as nome_curso',
            'cursos.titulo as titulo_curso',
        )
        ->join('clientes', 'matriculas.id_cliente', '=', 'clientes.id')
        ->join('cursos', 'matriculas.id_curso', '=', 'cursos.id')
        ->where('matriculas.excluido', 'n')
        ->where('matriculas.deletado', 'n');

        if (!empty($this->filters['situacao'])) {
            $val = $this->filters['situacao'];
            if (is_string($val) && str_contains($val, ',')) {
                $q->whereIn('matriculas.situacao', explode(',', $val));
            } else {
                $q->where('matriculas.situacao', $val);
            }
        }
        if (!empty($this->filters['id_curso'])) {
            $q->where('matriculas.id_curso', $this->filters['id_curso']);
        }
        if (!empty($this->filters['tipo_curso'])) {
            $val = $this->filters['tipo_curso'];
            if (is_string($val) && str_contains($val, ',')) {
                $q->whereIn('cursos.tipo', explode(',', $val));
            } else {
                $q->where('cursos.tipo', $val);
            }
        }
        if (!empty($this->filters['id_turma'])) {
            $val = $this->filters['id_turma'];
            if (is_string($val) && str_contains($val, ',')) {
                $q->whereIn('matriculas.id_turma', explode(',', $val));
            } else {
                $q->where('matriculas.id_turma', $val);
            }
        }
        if (!empty($this->filters['id_cliente'])) {
            $q->where('matriculas.id_cliente', $this->filters['id_cliente']);
        }
        if (!empty($this->filters['status'])) {
            $val = $this->filters['status'];
            if (is_string($val) && str_contains($val, ',')) {
                $q->whereIn('matriculas.status', explode(',', $val));
            } else {
                $q->where('matriculas.status', $val);
            }
        }

        $q->orderBy('matriculas.id', 'asc');

        $data = $q->get();

        if ($data->count()) {
            $ids = $data->pluck('id')->toArray();
            $metas = DB::table('matriculameta')
                ->whereIn('matricula_id', $ids)
                ->get()
                ->groupBy('matricula_id');

            foreach ($data as $row) {
                $metaArr = isset($metas[$row->id])
                    ? $metas[$row->id]->pluck('meta_value', 'meta_key')->toArray()
                    : [];
                foreach ($metaArr as $k => $v) {
                    $decoded = json_decode($v, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $metaArr[$k] = $decoded;
                    }
                }
                $row->metacampos = $metaArr;
            }
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Aluno',
            'Nome',
            'Sobrenome',
            'Telefone Zap',
            'Telefone',
            'Email',
            'CPF',
            'Nacionalidade',
            'Endereco',
            'Numero',
            'Bairro',
            'Cidade',
            'UF',
            'CEP',
            'Complemento',
            'Identidade',
            'Data Nascimento',
            'Estado Civil',
            'Profissao',
            'ID Cliente',
            'ID Curso',
            'Curso',
            'Titulo Curso',
            'Tipo Curso',
            'ID Turma',
            'Status',
            'Situacao',
            'Data Matricula',
            'Data Contrato',
            'Data Conclusao',
            'Total',
            'Subtotal',
            'Desconto',
            'Token',
            'Data Criacao',
            'Data Atualizacao',
            'Metacampos',
        ];
    }

    public function map($row): array
    {
        $situacaoMap = ['n' => 'Nada', 'a' => 'Andamento', 'p' => 'Perda', 'g' => 'Ganho'];

        return [
            $row->id,
            $row->aluno,
            $row->Nome,
            $row->sobrenome,
            $row->telefonezap,
            $row->Tel,
            $row->Email,
            $row->cpf_aluno,
            $row->nacionalidade,
            $row->Endereco,
            $row->Numero,
            $row->Bairro,
            $row->Cidade,
            $row->Uf,
            $row->cep,
            $row->Compl,
            $row->identidade,
            $row->data_nascimento,
            $row->estado_civil,
            $row->profissao,
            $row->id_cliente,
            $row->id_curso,
            $row->nome_curso,
            $row->titulo_curso,
            $row->tipo_curso,
            $row->id_turma,
            $row->status,
            $situacaoMap[$row->situacao] ?? $row->situacao,
            $row->data_matricula,
            $row->data_contrato,
            $row->data_conclusao,
            $row->total,
            $row->subtotal,
            $row->desconto,
            $row->token,
            $row->data,
            $row->atualizado,
            json_encode($row->metacampos ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ];
    }
}
