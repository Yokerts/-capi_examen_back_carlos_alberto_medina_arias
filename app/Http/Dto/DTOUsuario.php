<?php

namespace App\Http\Dto;

use App\Http\Controllers\Controller;

class DTOUsuario extends Controller
{
    protected $id_usuario;
    protected $id_user;
    protected $id_rocket_chat;
    protected $usuario_rocket_chat;
    protected $nombre;
    protected $apellido_paterno;
    protected $apellido_materno;
    protected $fecha_nacimiento;
    protected $id_cat_sexo;
    protected $celular;
    protected $telefono;
    protected $correo_electronico;
    protected $foto;
    protected $id_cat_tipo_usuario;
    protected $id_cat_estado_nacimiento;
    protected $id_cat_municipio_nacimiento;
    protected $curp;
    protected $rfc;
    protected $registro_verificacion_status;
    protected $registro_verificacion_codigo;
    protected $olvido_verificacion_status;
    protected $olvido_verificacion_codigo;
    protected $cambiar_verificacion_codigo;
    protected $ultima_sesion;
    protected $activo;
    protected $player_id;
    protected $sendmail;
    protected $isjefe;
    protected $isjefeplaza;
    protected $id_plaza;


    protected $filtro_fecha_alta_inicio;
    protected $filtro_fecha_alta_fin;
    protected $filtro_usuario;
    protected $filtro_correo_electronico;
    protected $filtro_id_cat_tipo_usuario;
    protected $filtro_id_plaza;
    protected $filtro_activo;

    protected $page;
    protected $limit;

    /**
     * DTOUsuario constructor.
     */
    public function __construct()
    {
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
    public function getIdUser()
    {
        return $this->id_user;
    }

    /**
     * @param mixed $id_user
     */
    public function setIdUser($id_user): void
    {
        $this->id_user = $id_user;
    }

    /**
     * @return mixed
     */
    public function getIdRocketChat()
    {
        return $this->id_rocket_chat;
    }

    /**
     * @param mixed $id_rocket_chat
     */
    public function setIdRocketChat($id_rocket_chat): void
    {
        $this->id_rocket_chat = $id_rocket_chat;
    }

    /**
     * @return mixed
     */
    public function getUsuarioRocketChat()
    {
        return $this->usuario_rocket_chat;
    }

    /**
     * @param mixed $usuario_rocket_chat
     */
    public function setUsuarioRocketChat($usuario_rocket_chat): void
    {
        $this->usuario_rocket_chat = $usuario_rocket_chat;
    }

    /**
     * @return mixed
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * @param mixed $nombre
     */
    public function setNombre($nombre): void
    {
        $this->nombre = $nombre;
    }

    /**
     * @return mixed
     */
    public function getApellidoPaterno()
    {
        return $this->apellido_paterno;
    }

    /**
     * @param mixed $apellido_paterno
     */
    public function setApellidoPaterno($apellido_paterno): void
    {
        $this->apellido_paterno = $apellido_paterno;
    }

    /**
     * @return mixed
     */
    public function getApellidoMaterno()
    {
        return $this->apellido_materno;
    }

    /**
     * @param mixed $apellido_materno
     */
    public function setApellidoMaterno($apellido_materno): void
    {
        $this->apellido_materno = $apellido_materno;
    }

    /**
     * @return mixed
     */
    public function getFechaNacimiento()
    {
        return $this->fecha_nacimiento;
    }

    /**
     * @param mixed $fecha_nacimiento
     */
    public function setFechaNacimiento($fecha_nacimiento): void
    {
        $this->fecha_nacimiento = $fecha_nacimiento;
    }

    /**
     * @return mixed
     */
    public function getIdCatSexo()
    {
        return $this->id_cat_sexo;
    }

    /**
     * @param mixed $id_cat_sexo
     */
    public function setIdCatSexo($id_cat_sexo): void
    {
        $this->id_cat_sexo = $id_cat_sexo;
    }

    /**
     * @return mixed
     */
    public function getCelular()
    {
        return $this->celular;
    }

    /**
     * @param mixed $celular
     */
    public function setCelular($celular): void
    {
        $this->celular = $celular;
    }

    /**
     * @return mixed
     */
    public function getTelefono()
    {
        return $this->telefono;
    }

    /**
     * @param mixed $telefono
     */
    public function setTelefono($telefono): void
    {
        $this->telefono = $telefono;
    }

    /**
     * @return mixed
     */
    public function getCorreoElectronico()
    {
        return $this->correo_electronico;
    }

    /**
     * @param mixed $correo_electronico
     */
    public function setCorreoElectronico($correo_electronico): void
    {
        $this->correo_electronico = $correo_electronico;
    }

    /**
     * @return mixed
     */
    public function getFoto()
    {
        return $this->foto;
    }

    /**
     * @param mixed $foto
     */
    public function setFoto($foto): void
    {
        $this->foto = $foto;
    }

    /**
     * @return mixed
     */
    public function getIdCatTipoUsuario()
    {
        return $this->id_cat_tipo_usuario;
    }

    /**
     * @param mixed $id_cat_tipo_usuario
     */
    public function setIdCatTipoUsuario($id_cat_tipo_usuario): void
    {
        $this->id_cat_tipo_usuario = $id_cat_tipo_usuario;
    }

    /**
     * @return mixed
     */
    public function getIdCatEstadoNacimiento()
    {
        return $this->id_cat_estado_nacimiento;
    }

    /**
     * @param mixed $id_cat_estado_nacimiento
     */
    public function setIdCatEstadoNacimiento($id_cat_estado_nacimiento): void
    {
        $this->id_cat_estado_nacimiento = $id_cat_estado_nacimiento;
    }

    /**
     * @return mixed
     */
    public function getIdCatMunicipioNacimiento()
    {
        return $this->id_cat_municipio_nacimiento;
    }

    /**
     * @param mixed $id_cat_municipio_nacimiento
     */
    public function setIdCatMunicipioNacimiento($id_cat_municipio_nacimiento): void
    {
        $this->id_cat_municipio_nacimiento = $id_cat_municipio_nacimiento;
    }

    /**
     * @return mixed
     */
    public function getCurp()
    {
        return $this->curp;
    }

    /**
     * @param mixed $curp
     */
    public function setCurp($curp): void
    {
        $this->curp = $curp;
    }

    /**
     * @return mixed
     */
    public function getRfc()
    {
        return $this->rfc;
    }

    /**
     * @param mixed $rfc
     */
    public function setRfc($rfc): void
    {
        $this->rfc = $rfc;
    }

    /**
     * @return mixed
     */
    public function getRegistroVerificacionStatus()
    {
        return $this->registro_verificacion_status;
    }

    /**
     * @param mixed $registro_verificacion_status
     */
    public function setRegistroVerificacionStatus($registro_verificacion_status): void
    {
        $this->registro_verificacion_status = $registro_verificacion_status;
    }

    /**
     * @return mixed
     */
    public function getRegistroVerificacionCodigo()
    {
        return $this->registro_verificacion_codigo;
    }

    /**
     * @param mixed $registro_verificacion_codigo
     */
    public function setRegistroVerificacionCodigo($registro_verificacion_codigo): void
    {
        $this->registro_verificacion_codigo = $registro_verificacion_codigo;
    }

    /**
     * @return mixed
     */
    public function getOlvidoVerificacionStatus()
    {
        return $this->olvido_verificacion_status;
    }

    /**
     * @param mixed $olvido_verificacion_status
     */
    public function setOlvidoVerificacionStatus($olvido_verificacion_status): void
    {
        $this->olvido_verificacion_status = $olvido_verificacion_status;
    }

    /**
     * @return mixed
     */
    public function getOlvidoVerificacionCodigo()
    {
        return $this->olvido_verificacion_codigo;
    }

    /**
     * @param mixed $olvido_verificacion_codigo
     */
    public function setOlvidoVerificacionCodigo($olvido_verificacion_codigo): void
    {
        $this->olvido_verificacion_codigo = $olvido_verificacion_codigo;
    }

    /**
     * @return mixed
     */
    public function getCambiarVerificacionCodigo()
    {
        return $this->cambiar_verificacion_codigo;
    }

    /**
     * @param mixed $cambiar_verificacion_codigo
     */
    public function setCambiarVerificacionCodigo($cambiar_verificacion_codigo): void
    {
        $this->cambiar_verificacion_codigo = $cambiar_verificacion_codigo;
    }

    /**
     * @return mixed
     */
    public function getUltimaSesion()
    {
        return $this->ultima_sesion;
    }

    /**
     * @param mixed $ultima_sesion
     */
    public function setUltimaSesion($ultima_sesion): void
    {
        $this->ultima_sesion = $ultima_sesion;
    }

    /**
     * @return mixed
     */
    public function getActivo()
    {
        return $this->activo;
    }

    /**
     * @param mixed $activo
     */
    public function setActivo($activo): void
    {
        $this->activo = $activo;
    }

    /**
     * @return mixed
     */
    public function getPlayerId()
    {
        return $this->player_id;
    }

    /**
     * @param mixed $player_id
     */
    public function setPlayerId($player_id): void
    {
        $this->player_id = $player_id;
    }

    /**
     * @return mixed
     */
    public function getSendmail()
    {
        return $this->sendmail;
    }

    /**
     * @param mixed $sendmail
     */
    public function setSendmail($sendmail): void
    {
        $this->sendmail = $sendmail;
    }

    /**
     * @return mixed
     */
    public function getIsjefe()
    {
        return $this->isjefe;
    }

    /**
     * @param mixed $isjefe
     */
    public function setIsjefe($isjefe): void
    {
        $this->isjefe = $isjefe;
    }

    /**
     * @return mixed
     */
    public function getIsjefeplaza()
    {
        return $this->isjefeplaza;
    }

    /**
     * @param mixed $isjefeplaza
     */
    public function setIsjefeplaza($isjefeplaza): void
    {
        $this->isjefeplaza = $isjefeplaza;
    }

    /**
     * @return mixed
     */
    public function getIdPlaza()
    {
        return $this->id_plaza;
    }

    /**
     * @param mixed $id_plaza
     */
    public function setIdPlaza($id_plaza): void
    {
        $this->id_plaza = $id_plaza;
    }

    /**
     * @return mixed
     */
    public function getFiltroFechaAltaInicio()
    {
        return $this->filtro_fecha_alta_inicio;
    }

    /**
     * @param mixed $filtro_fecha_alta_inicio
     */
    public function setFiltroFechaAltaInicio($filtro_fecha_alta_inicio): void
    {
        $this->filtro_fecha_alta_inicio = $filtro_fecha_alta_inicio;
    }

    /**
     * @return mixed
     */
    public function getFiltroFechaAltaFin()
    {
        return $this->filtro_fecha_alta_fin;
    }

    /**
     * @param mixed $filtro_fecha_alta_fin
     */
    public function setFiltroFechaAltaFin($filtro_fecha_alta_fin): void
    {
        $this->filtro_fecha_alta_fin = $filtro_fecha_alta_fin;
    }

    /**
     * @return mixed
     */
    public function getFiltroUsuario()
    {
        return $this->filtro_usuario;
    }

    /**
     * @param mixed $filtro_usuario
     */
    public function setFiltroUsuario($filtro_usuario): void
    {
        $this->filtro_usuario = $filtro_usuario;
    }

    /**
     * @return mixed
     */
    public function getFiltroCorreoElectronico()
    {
        return $this->filtro_correo_electronico;
    }

    /**
     * @param mixed $filtro_correo_electronico
     */
    public function setFiltroCorreoElectronico($filtro_correo_electronico): void
    {
        $this->filtro_correo_electronico = $filtro_correo_electronico;
    }

    /**
     * @return mixed
     */
    public function getFiltroIdCatTipoUsuario()
    {
        return $this->filtro_id_cat_tipo_usuario;
    }

    /**
     * @param mixed $filtro_id_cat_tipo_usuario
     */
    public function setFiltroIdCatTipoUsuario($filtro_id_cat_tipo_usuario): void
    {
        $this->filtro_id_cat_tipo_usuario = $filtro_id_cat_tipo_usuario;
    }

    /**
     * @return mixed
     */
    public function getFiltroIdPlaza()
    {
        return $this->filtro_id_plaza;
    }

    /**
     * @param mixed $filtro_id_plaza
     */
    public function setFiltroIdPlaza($filtro_id_plaza): void
    {
        $this->filtro_id_plaza = $filtro_id_plaza;
    }

    /**
     * @return mixed
     */
    public function getFiltroActivo()
    {
        return $this->filtro_activo;
    }

    /**
     * @param mixed $filtro_activo
     */
    public function setFiltroActivo($filtro_activo): void
    {
        $this->filtro_activo = $filtro_activo;
    }

    /**
     * @return mixed
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @param mixed $page
     */
    public function setPage($page): void
    {
        $this->page = $page;
    }

    /**
     * @return mixed
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param mixed $limit
     */
    public function setLimit($limit): void
    {
        $this->limit = $limit;
    }

}
