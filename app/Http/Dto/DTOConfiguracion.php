<?php

namespace App\Http\Dto;

use App\Http\Controllers\Controller;

class DTOConfiguracion extends Controller
{
    protected $id_configuracion;
    protected $id_usuario;
    protected $tiempo_toast;
    protected $tipo_menu;

    /**
     * DTOConfiguracion constructor.
     */
    public function __construct()
    {
    }

    /**
     * @return mixed
     */
    public function getIdConfiguracion()
    {
        return $this->id_configuracion;
    }

    /**
     * @param mixed $id_configuracion
     */
    public function setIdConfiguracion($id_configuracion): void
    {
        $this->id_configuracion = $id_configuracion;
    }

    /**
     * @return mixed
     */
    public function getIdUsuario()
    {
        return $this->id_usuario;
    }

    /**
     * @param mixed $id_usuario
     */
    public function setIdUsuario($id_usuario): void
    {
        $this->id_usuario = $id_usuario;
    }

    /**
     * @return mixed
     */
    public function getTiempoToast()
    {
        return $this->tiempo_toast;
    }

    /**
     * @param mixed $tiempo_toast
     */
    public function setTiempoToast($tiempo_toast): void
    {
        $this->tiempo_toast = $tiempo_toast;
    }

    /**
     * @return mixed
     */
    public function getTipoMenu()
    {
        return $this->tipo_menu;
    }

    /**
     * @param mixed $tipo_menu
     */
    public function setTipoMenu($tipo_menu): void
    {
        $this->tipo_menu = $tipo_menu;
    }

}
