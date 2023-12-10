<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use GuzzleHttp\Client;

class UserController extends Controller
{

    public function createUser(Request $request)
    {
        $this->validate($request, [
            'nome_completo' => 'required|string',
            'data_nascimento' => 'required|date|data_nascimento',
            'cpf' => 'required|cpf|unique:users',
            'email' => 'required|email|unique:users',
            'senha' => 'required|min:6',
            'endereco_numero' => 'required|numeric',
            'endereco_complemento' => 'nullable|string',
            'endereco_cep' => 'required|numeric|min:8',
        ]);

        $enderecoData = $this->obterDadosEndereco($request->input('endereco_cep'));
        if ($enderecoData != "") {
            $logradouro = $enderecoData['logradouro'];
            $cidade = $enderecoData['cidade'];
            $estado = $enderecoData['estado'];
        } else {
            // Lida com o caso em que a API ViaCep não retorna dados
            return $enderecoData;
        }

        User::create([
            'nome_completo' => $request->input('nome_completo'),
            'data_nascimento' => $request->input('data_nascimento'),
            'cpf' => $request->input('cpf'),
            'email' => $request->input('email'),
            'senha' => $request->input('senha'),
            'endereco_rua' => $logradouro,
            'endereco_numero' => $request->input('endereco_numero'),
            'endereco_complemento' => $request->input('endereco_complemento'),
            'endereco_cep' => $request->input('endereco_cep'),
            'endereco_cidade' => $cidade,
            'endereco_estado' => $estado,
            'saldo' => 0
        ]);

        // Retornar resposta ou redirecionar conforme necessário
        return response()->json(['message' => 'Usuário cadastrado com sucesso'], 201);
    }


    public function obterDadosEndereco($cep)
    {
        // URL da API ViaCep
        $url = "https://viacep.com.br/ws/{$cep}/json/";

        // Inicializa o cliente Guzzle
        $client = new Client();

        try {
            // Faz a requisição GET para a API ViaCep
            $response = $client->get($url);

            // Obtém os dados do corpo da resposta em formato JSON
            $data = json_decode($response->getBody(), true);

            // Verifica se a resposta da API contém dados
            if (isset($data['cep'])) {
                $logradouro = $data['logradouro'];
                $cidade = $data['localidade'];
                $estado = $data['uf'];

                // Faça o que precisar com os dados
                return [
                    'logradouro' => $logradouro,
                    'cidade' => $cidade,
                    'estado' => $estado,
                ];
            } else {
                // Resposta da API não contém dados
                return [
                    'error' => 'CEP não encontrado',
                ];
            }
        } catch (\Exception $e) {
            // Tratamento de erro
            return [
                'error' => 'Erro ao consultar a API ViaCep',
            ];
        }
    }

    public function me()
    {
        return response()->json(
            auth()->user()
        );
    }
    
    public function getUsers(){
        $users = User::all();
        // Retorna a resposta em JSON com os usuários
        return response()->json(["users" => $users]);
    }
    
    public function getUser($user_id){
        $user = User::find($user_id);

        // Retorna a resposta em JSON com os usuários
        return response()->json(["user" => $user]);
    }
}
