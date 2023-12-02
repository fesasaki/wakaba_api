<?php

namespace App\Models;

use App\Helper\IP as IPHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

/**
 * LogSystem
 *
 * Responsável por registrar todas as ações realizadas no sistema
 */
class LogSystem extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'action', 'level', 'user_id', 'history'
    ];

    protected $hidden = [
        //
    ];

    /**
     * Usuário
     *
     * Indica o usuário responsável pela ação
     * Registros sem usuário vinculado representam ações do sistema
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id')->select(['id', 'name'])->withDefault(['name' => 'Sistema']);
    }

    /**
     * Registrar log de informação
     *
     * @param string $action
     * @param int $user
     *
     * @return boolean
     */
    public function info($action, $user = null)
    {
        return $this->register($action, 1, $user);
    }


    /**
     * Registrar log de alerta
     *
     * @param string $action
     * @param int $user
     *
     * @return boolean
     */
    public function warn($action, $user = null)
    {
        return $this->register($action, 2, $user);
    }


    /**
     * Registrar log de erro
     *
     * @param string $action
     * @param int $user
     *
     * @return boolean
     */
    public function error($action, $user = null)
    {
        return $this->register($action, 3, $user);
    }


    /**
     * Registrar log de erro critico
     *
     * @param string $action
     * @param int $user
     *
     * @return boolean
     */
    public function critical($action, $user = null)
    {
        return $this->register($action, 4, $user);
    }

    /**
     * Registrar log com Historico
     *
     * @param string $action
     * @param Object $oldData
     * @param ParameterBag $newData
     *
     * @return boolean
     */
    public function logWithHistory($action, $old_data, $new_data)
    {

        $changes = [];
        $keys = $new_data->keys();

        // Passa por todos os campos enviados na operação
        foreach ($keys as $key) {

            // Pega o valor antigo e o novo
            $old = isset($old_data->$key) ? $old_data->$key : "";
            $new = $new_data->get($key);

            // Converte para string
            $old = $old === null ? "" : strval($old);
            $new = $new === null ? "" : strval($new);

            // Converte qualquer valor de senha vindos do banco para string vazia
            // Só será considerado alteração caso uma senha tiver sido enviada pelo usuário
            $old = strpos($key, 'password') !== false ? "" : $old;

            // Somente os valores forem diferentes gera um registro de histórico
            if ($old != $new) {

                // Se os campos contiverem password ou token no nome, substitui o valor real por asteriscos
                $old = strpos($key, 'password') !== false  || strpos($key, 'token') !== false ? '******' : $old;
                $new = strpos($key, 'password') !== false  || strpos($key, 'token') !== false ? '******' : $new;

                // Adiciona a alteração na lista
                $changes[] = ['field' => $key, 'old_value' => $old, 'new_value' => $new];
            }
        }

        // Só registra o log se houverem alterações
        if (count($changes) > 0) {

            $this->action = $action;
            $this->level = 1;
            $this->ip_address = IPHelper::getIp();
            $this->user_id = Auth::id();
            $this->history = json_encode($changes);

            return $this->save();
        } else {
            return true;
        }
    }


    /**
     * Registrar log
     *
     * @param string $action
     * @param int $level (1 - Info, 2 - Alerta, 3 - Erro, 4 - Erro Critio)
     * @param int $user
     *
     * @return boolean
     */
    private function register($action, $level, $user)
    {

        $this->action = $action;
        $this->level = $level;
        $this->ip_address = IPHelper::getIp();
        $this->user_id = $user ? $user : Auth::id();

        return $this->save();
    }

    public function inconsistency($action,  $history)
    {
        $this->action = $action;
        $this->level = 5;
        $this->ip_address = IPHelper::getIp();
        $this->user_id = Auth::id();
        $this->history = json_encode($history);

        return $this->save();
    }

    public function suspect($action,  $history)
    {
        $this->action = $action;
        $this->level = 6;
        $this->ip_address = IPHelper::getIp();
        $this->user_id = Auth::id();
        $this->history = json_encode($history);

        return $this->save();
    }

    public static function inconsistencyCount($type = null)
    {
        $query = self::with('user')
            ->where('level', 5)
            ->select('user_id', DB::raw('COUNT(*) as total'))
            ->groupBy('user_id')
            ->havingRaw('COUNT(*)')
            ->orderByDesc('total');

            if ($type === null || $type === '') {
                // Se a variável $type for nula ou vazia, busca todos os tipos de histórico
                $query->whereIn('history->type', [1,2,3,4,5,6,7]); // Adicione todos os tipos de histórico possíveis aqui
            } else {
                // Caso contrário, busca somente o tipo de histórico especificado
                $query->where('history->type', $type);
            }
            return $query;
    }

    public static function suspectCount()
    {
        return self::with('user')
            ->where('level', 6)
            ->select('user_id', DB::raw('COUNT(*) as total'))
            ->groupBy('user_id')
            ->havingRaw('COUNT(*)')
            ->orderByDesc('total');
    }
}
