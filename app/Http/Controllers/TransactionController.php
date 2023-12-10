<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


class TransactionController extends Controller
{
    public function deposit(Request $request)
    {
        // Validação dos dados do pedido de depósito
        $this->validate($request, [
            'value' => 'required|numeric|min:0|max:200000',
        ]);

        $userAuth = Auth::user();

        // Certifique-se de que o usuário está autenticado
        if (!$userAuth) {
            return response()->json(['message' => 'Usuário não autenticado.'], 401);
        }

        // Obtenha o ID do usuário de origem a partir do usuário autenticado

        $user = User::find($userAuth->id);

        // Criação da transação
        $authorizationCode = 'DEP' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);


        // Verifica se o usuário foi encontrado
        if ($user == null) {
            return response()->json(['message' => 'Usuário não encontrado.'], 400);
        }

        $value = $request->input('value');

        // Verifica se o valor está dentro dos limites permitidos
        if ($value <= 0 || $value >= 200000) {
            return response()->json(['message' => 'Valor deve ser maior que 0 e menor que R$ 200.000,00'], 422);
        }

        // Atualiza o saldo do usuário
        $novoSaldo = $user->saldo + $value;

        // Cria a transação
        if (!Transaction::create([
            'user_id' => $user->id,
            'type' => 'DEP',
            'authorization_code' => $authorizationCode,
            'value' => $value,
        ])) {
            return response()->json(['message' => 'Erro ao processar depósito.'], 422);
        }

        // Atualiza o saldo do usuário no banco de dados
        $user->update(['saldo' => $novoSaldo]);

        // Retorna uma resposta JSON com sucesso e o novo saldo
        return response()->json([
            'message' => 'Depósito realizado com sucesso',
            'new_balance' => $novoSaldo,
        ], 200);
    }

    public function transfer(Request $request)
    {
        // Validação dos dados do pedido de transferência
        $this->validate($request, [
            'destinationAccountId' => 'required|exists:users,id',
            'value' => 'required|numeric|min:0',
        ]);
    
        $user = Auth::user();

        // Certifique-se de que o usuário está autenticado
        if (!$user) {
            return response()->json(['message' => 'Usuário não autenticado.'], 401);
        }

        // Obtenha o ID do usuário de origem a partir do usuário autenticado
        $originAccountId = $user->id;
        $destinationAccountId = $request->input('destinationAccountId');
        $value = $request->input('value');
    
        // Busca do usuário de origem
        $userOrigin = User::find($originAccountId);
        // Busca do usuário de destino
        $destinationAccount = User::find($destinationAccountId);
    
        // Verifica se ambos os usuários existem
        if ($userOrigin === null || $destinationAccount === null) {
            return response()->json(['message' => 'Usuário de origem ou destino não encontrado.'], 404);
        }
    
        // Verifica se o valor da transferência é maior que 0
        if ($value <= 0) {
            return response()->json(['message' => 'O valor da transferência deve ser maior que 0.'], 400);
        }
    
        // Verifica se o usuário de origem possui saldo suficiente
        if ($userOrigin->saldo < $value) {
            return response()->json(['message' => 'Saldo insuficiente para realizar a transferência'], 400);
        }
    
        // Utilização de transações de banco de dados para garantir consistência
        DB::transaction(function () use ($userOrigin, $destinationAccount, $originAccountId, $destinationAccountId, $value) {
            // Cria a transação para representar a transferência
            Transaction::create([
                'user_id' => $userOrigin->id,
                'type' => 'TRANSF',
                'authorization_code' => 'TRANSF' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
                'value' => $value,
                'source_account_id' => $originAccountId,
                'destination_account_id' => $destinationAccountId,
            ]);
    
            // Atualiza o saldo da conta de origem
            $userOrigin->saldo -= $value;
            $userOrigin->save();
    
            // Atualiza o saldo da conta de destino
            $destinationAccount->saldo += $value;
            $destinationAccount->save();
        });
    
        return response()->json(['message' => 'Transferência realizada com sucesso'], 200);
    }
    


    public function getHistory($user_id)
    {
        // Verifica se o usuário existe
        $user = User::find($user_id);

        if ($user == null) {
            return response()->json(['message' => 'Usuário não encontrado.'], 404);
        }

        // Obtém o histórico de transações para o usuário
        $transactions = Transaction::where('user_id', $user_id)->get();

        // Monta a resposta JSON com o histórico de transações
        $response = [
            'user_id' => $user->id,
            'nome_completo' => $user->nome_completo,
            'transacoes' => $transactions,
        ];

        return response()->json($response, 200);
    }
}
