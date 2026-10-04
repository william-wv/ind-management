<?php

namespace App\Models;

use App\Services\ProfileAvatar;
use Core\Database\ActiveRecord\Model;
use Lib\Validations;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $role
 * @property string $encrypted_password
 * @property string $avatar_name
 */
class User extends Model
{
    // TODO 1: nome da tabela e colunas graváveis
    protected static string $table = 'users';
    protected static array $columns = ['name', 'email', 'encrypted_password', 'role'];

    // TODO 2: props virtuais (NÃO existem no banco)
    protected ?string $password = null;
    protected ?string $confirmed_password = null;

    // TODO 3: regras de validação (Model::isValid() chama isso antes do save)
    // Dica: Validations::notEmpty('campo', $this);
    //   - name, email, role -> não vazios
    //   - email -> único (Validations::uniqueness)
    //   - confirmação de senha -> SÓ se for registro novo ($this->newRecord())


    public function validates(): void
    {
        Validations::notEmpty('name', $this);
        Validations::notEmpty('email', $this);
        Validations::notEmpty('encrypted_password', $this);
        Validations::notEmpty('role', $this);
        Validations::uniqueness('email', $this);
        
        if($this->newRecord()){
            Validations::passwordConfirmation($this);
        }

    }


    // TODO 4: conferir senha digitada contra o hash do banco
    public function authenticate(string $password): bool
    {
        if($this->encrypted_password == null){
            return false;
        }

        return password_verify($password, $this->encrypted_password);
    }


    // TODO 5: buscar usuário pelo e-mail (null se não achar)
    // Dica: Model::findBy(['coluna' => valor]) -> ?static
    //   Dentro de método static, use `static::` ou `self::`
    public static function findByEmail(string $email): User | null
    {
        return Model::findBy(['email' => $email]);
    }


    // TODO 6: gerar hash quando `password` for atribuído
    // Dica: __set roda toda vez que você faz $user->algo = valor
    //   1. chamar parent::__set($property, $value) (senão nada é gravado)
    //   2. se $property === 'password' E registro novo E valor não vazio
    //      -> $this->encrypted_password = password_hash($value, PASSWORD_DEFAULT)  
    public function __set(string $property, mixed $value): void
    {
    }


    // TODO 7: avatar do usuário
    // Dica: App\Services\ProfileAvatar recebe o próprio usuário no construtor
    public function avatar(): ProfileAvatar
    {
    }
}
