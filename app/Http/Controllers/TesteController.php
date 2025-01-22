<?php

namespace App\Http\Controllers;

use App\Qlib\Qlib;
use Illuminate\Http\Request;

class TesteController extends Controller
{
    public function index(Request $request){
        $ret['exec'] = false;
        // $ret = (new SiteController())->short_code('fundo_proposta',['compl'=>'']);
        $token = $request->get('token');
        $ret = (new MatriculasController)->gerar_orcamento($token);
        // $ret = Qlib::qoption('validade_orcamento');
        // $ret = Qlib::dados_tab('cursos',['id' => 97]);
        // $rd = new RdstationController;
        // dd($rd->token_api);
         $data = [
            'nome' => 'José queta fernando',
            'email' => 'joao@example.com',
            'idade' => 40,
        ];
        $data = '{
  "event_name": "crm_deal_created",
  "document": {
    "id": "67900a6ebdb507001b85af2e",
    "name": "Teste de integração",
    "amount_monthly": 0,
    "amount_unique": 0,
    "amount_total": 0,
    "prediction_date": null,
    "created_at": "2025-01-21T17:58:22.813-03:00",
    "updated_at": "2025-01-21T17:58:22.813-03:00",
    "rating": 1,
    "status": "ongoing",
    "closed_at": null,
    "user": {
      "id": "6782896a7b00cb0014292693",
      "name": "Fernando Queta",
      "email": "quetafernando1@gmail.com",
      "avatar_url": null
    },
    "deal_stage": {
      "id": "676f057a09ad790016f37bb0",
      "name": "Pedido de Atendimento",
      "nickname": "PDA",
      "created_at": "2024-12-27T16:52:26.294-03:00",
      "updated_at": "2025-01-12T11:39:33.599-03:00",
      "order": 1
    },
    "deal_pipeline": {
      "id": "676f057a09ad790016f37bae",
      "name": "Aeroclube Comercial Vendas"
    },
    "deal_source": {
      "id": "676f057a09ad790016f37bab",
      "name": "Prospecção Ativa"
    },
    "campaign": {},
    "deal_lost_reason": {},
    "deal_custom_fields": [
      {
        "value": "",
        "custom_field": {
          "id": "67767ff61a1e040014e1e8a3",
          "label": "Link da Proposta",
          "required": true,
          "unique": false,
          "opts": [],
          "type": "text"
        }
      },
      {
        "value": "",
        "custom_field": {
          "id": "677680952d52460014190ae5",
          "label": "Link WhatsApp",
          "required": true,
          "unique": false,
          "opts": [],
          "type": "text"
        }
      },
      {
        "value": "",
        "custom_field": {
          "id": "6776d21a956c5100195daaca",
          "label": "Comissão sobre a venda",
          "required": false,
          "unique": false,
          "opts": [],
          "type": "text"
        }
      },
      {
        "value": [],
        "custom_field": {
          "id": "67812c0109e15d001a10e077",
          "label": "Curso de Interesse",
          "required": false,
          "unique": false,
          "opts": [
            "Ciências Aeronáuticas",
            "Piloto Privado Teórico",
            "Piloto Privado Prático",
            "Piloto Comercial Teórico",
            "Piloto Comercial Prático",
            "Outros"
          ],
          "type": "multiple_choice"
        }
      },
      {
        "value": [],
        "custom_field": {
          "id": "678024f0a8a0de00274cce6b",
          "label": "Suíte Numero",
          "required": true,
          "unique": false,
          "opts": [
            "201",
            "202",
            "203",
            "204",
            "205",
            "206",
            "207",
            "208",
            "209",
            "210",
            "211",
            "212",
            "Nenhuma das Opções"
          ],
          "type": "multiple_choice"
        }
      },
      {
        "value": [],
        "custom_field": {
          "id": "6780255a9231fb0014d81505",
          "label": "Alojamento Número",
          "required": true,
          "unique": false,
          "opts": [
            "Alojamento 1",
            "Alojamento 2",
            "Alojamento 3",
            "Alojamento 4",
            "Alojamento 5",
            "Alojamento 6",
            "Alojamento 7",
            "Alojamento 8",
            "Nenhuma das Opções"
          ],
          "type": "multiple_choice"
        }
      },
      {
        "value": [
          "a"
        ],
        "custom_field": {
          "id": "67853c9d54ccb4001496a060",
          "label": "Tags",
          "required": true,
          "unique": false,
          "opts": [
            "a",
            "ENTROU PELO SITE PRINCIPAL  ",
            "JANEIRO 2025  ",
            "SUDESTE ",
            "Matriculados 2024",
            "ENTROU ANÚNCIO PPA PRÁTICO  ",
            "DEZEMBRO 2024  ",
            "SUDESTE  ",
            "CURSO TEÓRICO DE PILOTO PRIVADO AVIÃO - MENTORIA  ",
            "MANUTENÇÃO",
            "CONTROLE TÉCNICO MANUTENÇÃO  ",
            "Fevereiro",
            "Plano de Formação ",
            "Matriculado Plano de Formação - Ensino superior  ",
            "MATRICULADO NO CURSO EAD  ",
            "PILOTO PRIVADO PRÁTICO  ",
            "ENTROU S/ ANÚNCIO  ",
            "OUTUBRO  ",
            "LEAD INDICAÇÃO  ",
            "CENTRO - OESTE  ",
            "veio do site principal"
          ],
          "type": "multiple_choice"
        }
      },
      {
        "value": null,
        "custom_field": {
          "id": "67800788c56de70017b80aba",
          "label": "Numero da O.S",
          "required": true,
          "unique": false,
          "opts": [],
          "type": "text"
        }
      }
    ],
    "deal_products": []
  },
  "event_timestamp": "2025-01-21T20:58:22.000Z",
  "transaction_uuid": "b2fe9543-c69a-4925-9070-c42230f71727"
}';
        $ret = Qlib::saveEditJson($data);
        return $ret;
    }
}
