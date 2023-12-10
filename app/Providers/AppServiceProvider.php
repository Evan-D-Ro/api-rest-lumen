<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Registre a regra de validação CPF
        Validator::extend('cpf', function ($attribute, $value, $parameters, $validator) {
            return $this->validaCPF($value);
        });

        Validator::extend('email', function ($email) {
            return $this->validaEmail($email);
        });

        Validator::extend('data_nascimento', function ($attribute, $value, $parameters, $validator) {
            // Obter a data atual
            $dataAtual = date('Y-m-d');
    
            // Validar se a data de nascimento é uma data válida
            $dataNascimento = date('Y-m-d', strtotime($value));
            if (!$dataNascimento) {
                return false;
            }
    
            // Comparar a data de nascimento com a data atual
            return $dataNascimento <= $dataAtual;
        });

    }
    public function register()
    {
        
    }
    function validaCPF($cpf) {
 
        // Extrai somente os números
        $cpf = preg_replace( '/[^0-9]/is', '', $cpf );
         
        // Verifica se foi informado todos os digitos corretamente
        if (strlen($cpf) != 11) {
            return false;
        }
    
        // Verifica se foi informada uma sequência de digitos repetidos. Ex: 111.111.111-11
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }
    
        // Faz o calculo para validar o CPF
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }
        return true;
    }

    function validaEmail($email) {
        $conta = "/^[a-zA-Z0-9\._-]+@";
        $domino = "[a-zA-Z0-9\._-]+.";
        $extensao = "([a-zA-Z]{2,4})$/";
        $pattern = $conta.$domino.$extensao;
        if (preg_match($pattern, $email, $check))
          return true;
        else
          return false;
      }
}
