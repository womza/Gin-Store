<?php
/* -----------------------------------------------------------------------------
  Autor: ZhenIT Software http://ZhenIT.com
  Email: info@ZhenIT.com
  ----------------------------------------------------------------------------- */
if (!defined('_PS_VERSION_'))
    exit;

include_once(_PS_MODULE_DIR_.'/servired/class_registro.php');
class Servired extends PaymentModule {
    const INSTALL_SQL_FILE = 'install.sql';

    private $_html = '';
    private $_postErrors = array();

    public function __construct() {
        $this->name = 'servired';
        $this->tab = 'payments_gateways';
        $this->author = 'ZhenIT Software';
        $this->version = '3.1.0';

        // Array config con los datos de configuración
        $config = Configuration::getMultiple(array('SERVIRED_ENV',
                    'SERVIRED_CLAVE', 'SERVIRED_CLAVE_PRUEBAS', 'SERVIRED_TIPOFIRMA',
                    'SERVIRED_NOTIFICACION', 'SERVIRED_NOMBRE', 'SERVIRED_CODIGO',
                    'SERVIRED_TERMINAL', 'SERVIRED_MONEDA', 'SERVIRED_SSL', 'SERVIRED_IDIOMAS_ESTADO', 'SERVIRED_TRANS',
                    'SERVIRED_RECARGO','SERVIRED_REG_ESTADO'));

        // Establecer propiedades según los datos de configuración
        $this->env = (int)$config['SERVIRED_ENV'];
        switch ($this->env) {
            case 1:
                $this->urltpv =
                        "https://sis-t.redsys.es:25443/sis/realizarPago";
                $this->clave = $config['SERVIRED_CLAVE_PRUEBAS'];
                break;
            case 2:
                $this->urltpv =
                        "https://sis-i.sermepa.es:25443/sis/realizarPago";
                $this->clave = $config['SERVIRED_CLAVE_PRUEBAS'];
                break;
            case 3:
                $this->urltpv =
                        "http://test.zhenit.com/sermepa/PRE";
                $this->clave = 'qwertyasdf0123456789';
                break;
            default:
                $this->urltpv =
                        "https://sis.redsys.es/sis/realizarPago";
                $this->clave = $config['SERVIRED_CLAVE'];
        }
        if (isset($config['SERVIRED_TIPOFIRMA']))
            $this->tipofirma = $config['SERVIRED_TIPOFIRMA'];
        if (isset($config['SERVIRED_NOMBRE']))
            $this->notificacion = $config['SERVIRED_NOTIFICACION'];
        if (isset($config['SERVIRED_NOMBRE']))
            $this->nombre = $config['SERVIRED_NOMBRE'];
        if (isset($config['SERVIRED_CODIGO']))
            $this->codigo = $config['SERVIRED_CODIGO'];
        if (isset($config['SERVIRED_TERMINAL']))
            $this->terminal = $config['SERVIRED_TERMINAL'];
        if (isset($config['SERVIRED_MONEDA']))
            $this->moneda = $config['SERVIRED_MONEDA'];
        if (isset($config['SERVIRED_SSL']))
            $this->ssl = $config['SERVIRED_SSL'];
        if (isset($config['SERVIRED_IDIOMAS_ESTADO']))
            $this->idiomas_estado = $config['SERVIRED_IDIOMAS_ESTADO'];
        if (isset($config['SERVIRED_REG_ESTADO']))
            $this->reg_estado = $config['SERVIRED_REG_ESTADO'];
        if (isset($config['SERVIRED_TRANS']))
            $this->trans = $config['SERVIRED_TRANS'];
        if (isset($config['SERVIRED_RECARGO']))
            $this->recargo = $config['SERVIRED_RECARGO'];
        parent::__construct();

        $this->page = basename(__FILE__, '.php');
        $this->displayName = $this->l('Servired');
        $this->description = $this->l('Aceptar pagos con tarjeta v&iacute;a Servired').'<br/>Más módulos en <a href="http://modulosdepago.es/Prestashop-1.5">ModulosDePago.es</a>';

        // Mostrar aviso en la página principal de módulos si faltan datos de configuración.
        if (!isset($this->urltpv)
                OR !isset($this->clave)
                OR !isset($this->nombre)
                OR !isset($this->codigo)
                OR !isset($this->terminal)
                OR !isset($this->moneda)
                OR !isset($this->ssl)
                OR !isset($this->reg_estado)
                OR !isset($this->idiomas_estado)
                OR !isset($this->trans)
                OR !isset($this->tipofirma)
                OR !isset($this->notificacion))
            $this->warning = $this->l('Te faltan datos a configurar el m&oacute;dulo Servired.');
    }

    public function install() {
        //Instala la tabla para almacenar las operaciones en la pasarela
        if (!file_exists(dirname(__FILE__).'/'.self::INSTALL_SQL_FILE))
            return (false);
        else if (!$sql = file_get_contents(dirname(__FILE__).'/'.self::INSTALL_SQL_FILE))
            return (false);
        $sql = str_replace('PREFIX_', _DB_PREFIX_.basename(dirname(__FILE__)), $sql);
        $sql = preg_split("/;\s*[\r\n]+/",$sql);
        foreach ($sql AS $k=>$query)
            Db::getInstance()->Execute(trim($query));

        Configuration::updateValue('SERVIRED_ENV', '0');
        Configuration::updateValue('SERVIRED_CLAVE_PRUEBAS', 'qwertyasdf0123456789');
        Configuration::updateValue('SERVIRED_NOMBRE', 'Escribe el nombre de tu tienda');
        Configuration::updateValue('SERVIRED_TERMINAL', 1);
        Configuration::updateValue('SERVIRED_MONEDA', '978');
        Configuration::updateValue('SERVIRED_SSL', 0);
        Configuration::updateValue('SERVIRED_IDIOMAS_ESTADO', 0);
        Configuration::updateValue('SERVIRED_REG_ESTADO', 0);
        Configuration::updateValue('SERVIRED_TRANS', 0);
        Configuration::updateValue('SERVIRED_TIPOFIRMA', 0);
        Configuration::updateValue('SERVIRED_NOTIFICACION', 0);
        Configuration::updateValue('SERVIRED_RECARGO', '0:0:0');
        // Valores por defecto al instalar el módulo
        if (!parent::install() OR !$this->registerHook('payment') OR !$this->registerHook('paymentReturn'))
            return false;
        return true;
    }

    public function uninstall() {
        // Valores a quitar si desinstalamos el módulo
        if (!Configuration::deleteByName('SERVIRED_URLTPV')
                OR !Configuration::deleteByName('SERVIRED_CLAVE')
                OR !Configuration::deleteByName('SERVIRED_CLAVE_PRUEBAS')
                OR !Configuration::deleteByName('SERVIRED_CODIGO')
                OR !Configuration::deleteByName('SERVIRED_TERMINAL')
                OR !Configuration::deleteByName('SERVIRED_MONEDA')
                OR !Configuration::deleteByName('SERVIRED_SSL')
                OR !Configuration::deleteByName('SERVIRED_IDIOMAS_ESTADO')
                OR !Configuration::deleteByName('SERVIRED_REG_ESTADO')
                OR !Configuration::deleteByName('SERVIRED_TRANS')
                OR !Configuration::deleteByName('SERVIRED_TIPOFIRMA')
                OR !Configuration::deleteByName('SERVIRED_NOTIFICACION')
                OR !Configuration::deleteByName('SERVIRED_RECARGO')
                OR !parent::uninstall())
            return false;
    }

    private function _postValidation() {

        // Si al enviar los datos del formulario de configuración hay campos vacios, mostrar errores.
        if (isset($_POST['btnSubmit'])) {
            if (empty($_POST['clave']))
                $this->_postErrors[] = $this->l('Se requiere la Clave secreta de encriptaci&oacute;n.');
            if (empty($_POST['nombre']))
                $this->_postErrors[] = $this->l('Se requiere el Nombre del comercio.');
            if (empty($_POST['codigo']))
                $this->_postErrors[] = $this->l('Se requiere el N&uacute;mero de comercio (FUC).');
            if (empty($_POST['terminal']))
                $this->_postErrors[] = $this->l('Se requiere el N&uacute;mero de terminal.');
            if (empty($_POST['moneda']))
                $this->_postErrors[] = $this->l('Se requiere el Tipo de moneda.');
        }
    }

    private function _postProcess() {
        // Actualizar la configuración en la BBDD
        if (isset($_POST['btnSubmit'])) {
            Configuration::updateValue('SERVIRED_ENV', $_POST['env']);
            Configuration::updateValue('SERVIRED_CLAVE', $_POST['clave']);
            Configuration::updateValue('SERVIRED_CLAVE_PRUEBAS', $_POST['clave_pruebas']);
            Configuration::updateValue('SERVIRED_TIPOFIRMA', $_POST['tipofirma']);
            Configuration::updateValue('SERVIRED_NOTIFICACION', $_POST['notificacion']);
            Configuration::updateValue('SERVIRED_NOMBRE', $_POST['nombre']);
            Configuration::updateValue('SERVIRED_CODIGO', $_POST['codigo']);
            Configuration::updateValue('SERVIRED_TERMINAL', $_POST['terminal']);
            Configuration::updateValue('SERVIRED_MONEDA', $_POST['moneda']);
            Configuration::updateValue('SERVIRED_SSL', (int)$_POST['ssl']);
            Configuration::updateValue('SERVIRED_REG_ESTADO', (int)$_POST['reg_estado']);
            Configuration::updateValue('SERVIRED_IDIOMAS_ESTADO', (int)$_POST['idiomas_estado']);
            Configuration::updateValue('SERVIRED_TRANS', $_POST['trans']);
            Configuration::updateValue('SERVIRED_RECARGO', $_POST['recargo']);
        }
    }

    private function _displayservired() {
        // Aparición el la lista de módulos
        $this->_html .= '<img src="../modules/servired/logo.png" style="float:left; margin-right:15px;"><b>' . $this->l('Este m&oacute;dulo te permite aceptar pagos con tarjeta.') . '</b><br /><br />
        ' . $this->l('Si el cliente elije este modo de pago, podr&aacute; pagar de forma autom&aacute;tica.') . '<br /><br /><br />';
    }

    private function _displayForm() {

        // Opciones para el select de monedas.
        $moneda = Tools::getValue('moneda', $this->moneda);
        $iseuro = ($moneda == '978') ? ' selected="selected" ' : '';
        $isdollar = ($moneda == '840') ? ' selected="selected" ' : '';
        // Opciones para activar/desactivar SSL
        $ssl = Tools::getValue('ssl', $this->ssl);
        $ssl_si = ($ssl == 1) ? ' checked="checked" ' : '';
        $ssl_no = ($ssl == 0) ? ' checked="checked" ' : '';
        // Opciones para activar los idiomas
        $idiomas_estado = Tools::getValue('idiomas_estado', $this->idiomas_estado);
        $idiomas_estado_si = ($idiomas_estado == 1) ? ' checked="checked" ' : '';
        $idiomas_estado_no = ($idiomas_estado == 0) ? ' checked="checked" ' :
        // Opciones para activar el registro de transacciones
        $reg_estado = '';
        $reg_estado = Tools::getValue('reg_estado', $this->reg_estado);
        $reg_estado_si = ( $reg_estado == 1) ? ' checked="checked" ' : '';
        $reg_estado_no = ( $reg_estado == 0) ? ' checked="checked" ' : '';
        // Opciones entorno
        $entorno = Tools::getValue('env', $this->env);
        $entorno_real = ($entorno == 0) ? ' selected="selected" ' : '';
        $entorno_i = ($entorno == 2) ? ' selected="selected" ' : '';
        $entorno_t = ($entorno == 1) ? ' selected="selected" ' : '';
        $entorno_z = ($entorno == 3) ? ' selected="selected" ' : '';

        // Opciones tipofirma
        $tipofirma = Tools::getValue('tipofirma', $this->tipofirma);
        $tipofirma_a = ($tipofirma == 0) ? ' checked="checked" ' : '';
        $tipofirma_c = ($tipofirma == 1) ? ' checked="checked" ' : '';

        // Opciones notificacion
        $notificacion = Tools::getValue('notificacion', $this->notificacion);
        $notificacion_s = ($notificacion == 1) ? ' checked="checked" ' : '';
        $notificacion_n = ($notificacion == 0) ? ' checked="checked" ' : '';

        // Mostar formulario
        $this->_html .=
                '<form action="' . $_SERVER['REQUEST_URI'] . '" method="post">
            <fieldset>
            <legend><img src="../img/admin/contact.gif" />' . $this->l('Configuraci&oacute;n del TPV') . '</legend>
                <table border="0" width="680" cellpadding="0" cellspacing="0" id="form">
                    <tr><td colspan="2">' . $this->l('Por favor completa la informaci&oacute;n requerida que te proporcionar&aacute; tu banco Servired.') . '.<br /><br /></td></tr>
                    <tr><td width="215" style="height:
35px;">' . $this->l('Entorno de servired') . '</td><td><select
name="env">
                        <option
value="0"' . $entorno_real . '>' . $this->l('Real') . '</option>
                        <option
value="1"' . $entorno_t . '>' . $this->l('Pruebas en sis-t') . '</option>
                        <option
value="2"' . $entorno_i . '>' . $this->l('Pruebas en sis-i') . '</option>
                        <option
value="3"' . $entorno_z . '>' . $this->l('Simulador ZhenIT (sólo para mantenimiento)') . '</option>
                    </select></td></tr>
                    <tr><td width="215" style="height: 35px;">' . $this->l('Clave
secreta de encriptaci&oacute;n') . '</td><td><input name="clave" type="text"
value="' . Configuration::get('SERVIRED_CLAVE') . '" style="width: 200px;"
/></td></tr>
                    <tr><td width="215" style="height: 35px;">' . $this->l('Clave
secreta de encriptaci&oacute;n (PRUEBAS)') . '</td><td><input type="text"
name="clave_pruebas" value="' . Configuration::get('SERVIRED_CLAVE_PRUEBAS') . '" style="width: 200px;" /></td></tr>
                    <tr><td width="215" style="height: 35px;">' . $this->l('Nombre del comercio') . '</td><td><input type="text" name="nombre" value="' . htmlentities(Tools::getValue('nombre', $this->nombre), ENT_COMPAT, 'UTF-8') . '" style="width: 200px;" /></td></tr>
                    <tr><td width="215" style="height: 35px;">' . $this->l('N&uacute;mero de comercio (FUC)') . '</td><td><input type="text" name="codigo" value="' . Tools::getValue('codigo', $this->codigo) . '" style="width: 200px;" /></td></tr>
                    <tr><td width="215" style="height:
35px;">' . $this->l('N&uacute;mero de terminal') . '</td><td><input type="text"
name="terminal" value="' . Tools::getValue('terminal', $this->terminal) . '"
style="width: 80px;" /></td></tr>
                    <tr><td width="215" style="height:
35px;">' . $this->l('Tipo de firma') . '</td>
                        <td>
                        <input type="radio" name="tipofirma"
id="tipofirma_c" value="1"' . $tipofirma_c . '/>' . $this->l('Completa') . '
                        <input type="radio" name="tipofirma"
id="tipofirma_a" value="0"' . $tipofirma_a . '/>' . $this->l('Ampliada') . '
                        </td>
                    </tr>
                    <tr><td width="215" style="height:
35px;">' . $this->l('Recargo ( porcentaje:fijo:m&iacute;nimo )') . '</td><td><input
type="text"
name="recargo" value="' . Tools::getValue('recargo', $this->recargo) . '"
style="width: 80px;" /></td></tr>
                    <tr><td width="215" style="height: 35px;">' . $this->l('Tipo de moneda') . '</td><td>
                    <select name="moneda" style="width: 80px;"><option
value=""></option><option value="978"' . $iseuro . '>EURO</option><option
value="840"' . $isdollar . '>DOLLAR</option></select></td></tr>

                    <tr><td width="215" style="height: 35px;">' . $this->l('Tipo de transacci&oacute;n') . '</td><td><input type="text" name="trans" value="' . Tools::getValue('trans', $this->trans) . '" style="width: 80px;" /></td></tr>


                    </td></tr>

                </table>
            </fieldset>
            <br>
            <fieldset>
            <legend><img src="../img/t/AdminPreferences.gif" />' . $this->l('Personalizaci&oacute;n') . '</legend>
            <table border="0" width="680" cellpadding="0" cellspacing="0" id="form">
        <tr>
            <td colspan="2">' . $this->l('Por favor completa los datos adicionales.') . '.<br /><br /></td>
        </tr>
        <tr><td width="215" style="height:
35px;">' . $this->l('Notificaci&oacute;n HTTP') . '</td>
            <td>
            <input type="radio" name="notificacion"
id="notificacion_1" value="1"' . $notificacion_s . '/>
            <img
src="../img/admin/enabled.gif"      alt="' . $this->l('Activado') . '"
title="' . $this->l('Activado') . '" />
            <input type="radio" name="notificacion"
id="notificacion_0" value="0"' . $notificacion_n . '/>
            <img src="../img/admin/disabled.gif"
alt="' . $this->l('Desactivado') . '"
title="' . $this->l('Desactivado') . '" />
            </td>
        </tr>
        <tr>
        <td width="215" style="height: 35px;">' . $this->l('SSL en URL de validaci&oacute;n') . '</td>
            <td>
            <input type="radio" name="ssl" id="ssl_1" value="1" ' . $ssl_si . '/>
            <img src="../img/admin/enabled.gif" alt="' . $this->l('Activado') . '" title="' . $this->l('Activado') . '" />
            <input type="radio" name="ssl" id="ssl_0" value="0" ' . $ssl_no . '/>
            <img src="../img/admin/disabled.gif" alt="' . $this->l('Desactivado') . '" title="' . $this->l('Desactivado') . '" />
            </td>
        </tr>
        <tr>
        <td width="215" style="height: 35px;">' . $this->l('Activar los idiomas en el TPV') . '</td>
            <td>
            <input type="radio" name="idiomas_estado" id="idiomas_estado_si" value="1" ' . $idiomas_estado_si . '/>
            <img src="../img/admin/enabled.gif" alt="' . $this->l('Activado') . '" title="' . $this->l('Activado') . '" />
            <input type="radio" name="idiomas_estado" id="idiomas_estado_no" value="0" ' . $idiomas_estado_no . '/>
            <img src="../img/admin/disabled.gif" alt="' . $this->l('Desactivado') . '" title="' . $this->l('Desactivado') . '" />
            </td>
        </tr>
        <tr>
        <td width="215" style="height: 35px;">' . $this->l('Activar registro de transacciones fallidas/incompletas') . '</td>
            <td>
            <input type="radio" name="reg_estado" id="reg_estado_si" value="1" ' . $reg_estado_si . '/>
            <img src="../img/admin/enabled.gif" alt="' . $this->l('Activado') . '" title="' . $this->l('Activado') . '" />
            <input type="radio" name="reg_estado" id="reg_estado_no" value="0" ' . $reg_estado_no . '/>
            <img src="../img/admin/disabled.gif" alt="' . $this->l('Desactivado') . '" title="' . $this->l('Desactivado') . '" />
            </td>
        </tr>
        </table>
            </fieldset>
            <br>
        <input class="button" name="btnSubmit" value="' . $this->l('Guardar configuraci&oacute;n') . '" type="submit" />
        </form>';
    }

    private function _displayFormView()
    {
        global $cookie;
        $servired_errorCodes = array (
                        '101' => 'Tarjeta caducada',
                        '102' => 'Tarjeta en excepción transitoria o bajo sospecha de fraude',
                        '106' => 'Intentos de PIN excedidos',
                        '125' => 'Tarjeta no efectiva',
                        '129' => 'Código de seguridad (CVV2/CVC2) incorrecto',
                        '180' => 'Tarjeta ajena al servicio',
                        '184' => 'Error en la autenticación del titular',
                        '190' => 'Denegación sin especificar Motivo',
                        '191' => 'Fecha de caducidad errónea',
                        '202' => 'Tarjeta en excepción transitoria o bajo sospecha de fraude con retirada de tarjeta',
                        '904' => 'Comercio no registrado en FUC',
                        '909' => 'Error de sistema',
                        '9912' => 'Emisor no disponible',
                        '912' => 'Emisor no disponible',
                        '950' => 'Operación de devolución no permitida',
                        '9064' => 'Número de posiciones de la tarjeta incorrecto',
                        '9078' => 'No existe método de pago válido para esa tarjeta',
                        '9093' => 'Tarjeta no existente',
                        '9218' => 'El comercio no permite op. seguras por entrada /operaciones',
                        '9253' => 'Tarjeta no cumple el check-digit',
                        '9256' => 'El comercio no puede realizar preautorizaciones',
                        '9257' => 'Esta tarjeta no permite operativa de preautorizaciones',
                        '9261' => 'Operación detenida por superar el control de restricciones en la entrada al SIS',
                        '9913' => 'Error en la confirmación que el comercio envía al TPV Virtual (solo aplicable en la opción de sincronización SOAP)',
                        '9914' => 'Confirmación “KO” del comercio (solo aplicable en la opción de sincronización SOAP)',
                        '9928' => 'Anulación de autorización en diferido realizada por el SIS (proceso batch)',
                        '9929' => 'Anulación de autorización en diferido realizada por el comercio',
                        '0' => 'No ha vuelto ha completado el proceso aún',
                    );
        if (Tools::isSubmit('id_cart'))
            $this->validateOrder($_GET['id_cart'], _PS_OS_PAYMENT_, $_GET['amount'], $this->displayName, NULL);
        if (Tools::isSubmit('id_registro'))
            class_registro::remove($_GET['id_registro']);

        $this->_html .= '<br /><br /><br />
        <form action="'.$_SERVER['REQUEST_URI'].'" method="post">
            <fieldset>
                <legend><img src="../img/admin/contact.gif" alt="" title="" />'.$this->l('Operaciones con errores').'</legend>';

        $carritos = class_registro::select();

        $this->_html .= '
        <table class="table">
            <thead>
                <tr>
                    <th class="item" style="text-align:center;width:150px;">'.$this->l('Fecha').'</th>
                    <th class="item" style="width:325px;">'.$this->l('Cliente').'</th>
                    <th class="item" style="text-align:center;width:75px;">'.$this->l('Importe').'</th>
                    <th class="item" style="text-align:center;width:300px;">'.$this->l('Tipo error').'</th>
                    <th class="item" style="text-align:center;width:50px;">'.$this->l('Acciones').'</th>
                </tr>
            </thead>
            <tbody>';
            foreach ($carritos as $registro)
            {
                $this->_html .= '
                    <tr>
                    <td class="first_item" style="text-align:center;">'.$registro['date_add'].'</td>
                    <td class="item" style="text-align:left;"><span>'.$registro['customer_firstname'].' '.$registro['customer_lastname'].'</span></td>
                    <td class="item" style="text-align:center;">'.$registro['amount'].'</td>
                    <td class="item" style="text-align:left;">'.$servired_errorCodes[$registro['error_code']].'</td>
                    <td class="center">';

                                $onClick = 'document.location = \''.AdminController::$currentIndex.'&configure='.$this->name.'&token='.$_GET['token'].'&amount='.$registro['amount'].'&id_cart='.$registro['id_cart'].'&id_registro='.$registro['id_registro'].'\'';
                                $this->_html .= '<img onClick="'.$onClick.'" src="../img/admin/add.gif" style="cursor:pointer" alt="'.$this->l('Crear Pedido').'" title="'.$this->l('Crear Pedido').'" />';

                                $onClick = 'document.location = "'.AdminController::$currentIndex.'&configure='.$this->name.'&token='.$_GET['token'].'&id_registro='.$registro['id_registro'].'"';
                                $this->_html .= '<img onClick=\'if (confirm("'.$this->l('Desea eliminar este error en el pago?').'")) '.$onClick.'\' style="cursor:pointer; margin-left:10px;" src="../img/admin/disabled.gif" alt="'.$this->l('Eliminar registro').'" title="'.$this->l('Eliminar registro').'" />

                    </td>
                </tr>';
            }
        $this->_html .= '
            </tbody>
            </table>
            </fieldset>
        </form>';

    }
    public function getContent() {
        // Recoger datos
        $this->_html = '<h2>' . $this->displayName . '</h2>';
        if (!empty($_POST)) {
            $this->_postValidation();
            if (!sizeof($this->_postErrors))
                $this->_postProcess();
            else
                foreach ($this->_postErrors AS $err)
                    $this->_html .= '<div class="alert error">' . $err . '</div>';
        }
        else
            $this->_html .= '<br />';
        $this->_displayservired();
        $this->_displayForm();
        if(Tools::getValue('reg_estado', $this->reg_estado)==1)
            $this->_displayFormView();
        return $this->_html;
    }

    public function hookPayment($params) {

        // Variables necesarias de fuera
        global $smarty, $cookie, $cart;

        // Valor de compra
        $id_currency = intval(Configuration::get('PS_CURRENCY_DEFAULT'));
        $fee = $this->getCost($params);
        $currency = new Currency(intval($id_currency));
        $cantidad = number_format(Tools::convertPrice($params['cart']->getOrderTotal(true, 3) + $fee, $currency), 2, '.', '');
        $cantidad = round($cantidad * 100);

        // El número de pedido es  los 8 ultimos digitos del ID del carrito + el tiempo MMSS.
        $numpedido = str_pad($params['cart']->id, 8, "0", STR_PAD_LEFT) . date('is');


        $codigo = Tools::getValue('codigo', $this->codigo);
        $moneda = Tools::getValue('moneda', $this->moneda);
        $trans = Tools::getValue('trans', $this->trans);

        $ssl = Tools::getValue('ssl', $this->ssl);
        $values = array(
            'id_cart' => (int)$params['cart']->id,
            'id_module' => (int)Module::getInstanceByName($this->name)->id,
            'id_order' => (int)Order::getOrderByCartId((int)$params['cart']->id),
            'key' => Context::getContext()->customer->secure_key
        );
        $urltienda = '';
        if (Tools::getValue('notificacion', $this->notificacion) > 0)
            $urltienda = Context::getContext()->link->getModuleLink($this->name, 'notify',array(),$ssl);

        $clave = Tools::getValue('clave', $this->clave);

        // Cálculo del SHA1
        if (Tools::getValue('tipofirma', $this->tipofirma))
            $mensaje = $cantidad . $numpedido . $codigo . $moneda . $clave;
        else
            $mensaje = $cantidad . $numpedido . $codigo . $moneda . $trans .
                    $urltienda . $clave;
        $firma = strtoupper(sha1($mensaje));

        $products = $params['cart']->getProducts();
        $productos = '';
        $id_cart = intval($params['cart']->id);

        //Activación de los idiomas del TPV
        $idiomas_estado = Tools::getValue('idiomas_estado', $this->idiomas_estado);
        if ($idiomas_estado) {
            $ps_language = new Language(intval($cookie->id_lang));
            $idioma_web = $ps_language->iso_code;
            switch ($idioma_web) {
                case 'es':
                    $idioma_tpv = '001';
                    break;
                case 'en':
                    $idioma_tpv = '002';
                    break;
                case 'ca':
                    $idioma_tpv = '003';
                    break;
                case 'fr':
                    $idioma_tpv = '004';
                    break;
                case 'de':
                    $idioma_tpv = '005';
                    break;
                case 'nl':
                    $idioma_tpv = '006';
                    break;
                case 'it':
                    $idioma_tpv = '007';
                    break;
                case 'sv':
                    $idioma_tpv = '008';
                    break;
                case 'pt':
                    $idioma_tpv = '009';
                    break;
                case 'pl':
                    $idioma_tpv = '011';
                    break;
                case 'gl':
                    $idioma_tpv = '012';
                    break;
                case 'eu':
                    $idioma_tpv = '013';
                    break;
                default:
                    $idioma_tpv = '002';
            }
        } else {
            $idioma_tpv = '0';
        }


        foreach ($products as $product) {
            $productos .= $product['quantity'] . ' ' . $product['name'] . "\n";
        }

        $smarty->assign(array(
        'urltpv' => Tools::getValue('urltpv', $this->urltpv),
        'cantidad' => $cantidad,
        'moneda' => $moneda,
        'pedido' => $numpedido,
        'codigo' => $codigo,
        'terminal' => Tools::getValue('terminal', $this->terminal),
        'trans' => $trans,
        'titular' => ($cookie->logged ? $cookie->customer_firstname . ' ' . $cookie->customer_lastname : false),
        'nombre' => Tools::getValue('nombre', $this->nombre),
        'urltienda' => $urltienda,
        'notificacion' => Tools::getValue('notificacion', $this->notificacion),
        'productos' => $productos,
        'UrlOk' => Context::getContext()->link->getPageLink('order-confirmation',$ssl,null,$values),
        'UrlKO' => Context::getContext()->link->getModuleLink($this->name, 'ko'),
        'firma' => $firma,
        'idioma_tpv' => $idioma_tpv,
        'this_path' => $this->_path,
        'this_path_ssl' => Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/' . $this->name . '/',
        'fee_servired' => number_format($fee, 2, '.', '')
        ));
        return $this->display(__FILE__, 'payment.tpl');
    }

    //Return the fee cost
    function getCost($params) {
        list($percent, $fix, $minimalfee) =
                explode(':', Configuration::get('SERVIRED_RECARGO'));
        $cartvalue = floatval($params['cart']->getOrderTotal(true, Cart::BOTH));
        $fee = $cartvalue * $percent / 100 + $fix;
        if ($fee < $minimalfee) {
            $fee = $minimalfee;
        }
        return round($fee,2);
    }

    //Return the fee cost
    function getCostValidated($cart) {

        list($percent, $fix, $minimalfee)
                = explode(':', Configuration::get('SERVIRED_RECARGO'));
        $cartvalue = floatval($cart->getOrderTotal(true, Cart::BOTH));
        $fee = $cartvalue * $percent / 100 + $fix;
        if ($fee < $minimalfee) {
            $fee = $minimalfee;
        }
        return round($fee,2);
    }

    public function processPN() {
        // Recoger datos de respuesta
        $total = $_REQUEST["Ds_Amount"];
        $pedido = $_REQUEST["Ds_Order"];
        $codigo = $_REQUEST["Ds_MerchantCode"];
        $moneda = $_REQUEST["Ds_Currency"];
        $respuesta = $_REQUEST["Ds_Response"];
        $firma_remota = $_REQUEST["Ds_Signature"];

        //Verificamos opciones
        $reg_estado = Configuration::get('SERVIRED_REG_ESTADO');
        // Contraseña secreta
        $clave = $this->clave;

        // Cálculo del SHA1
        $mensaje = $total . $pedido . $codigo . $moneda . $respuesta . $clave;
        $firma_local = strtoupper(sha1($mensaje));
        if ($firma_local == $firma_remota) {
            $total = number_format($total / 100, 4, '.', '');
            $pedido = substr($pedido, 0, 8);
            $pedido = intval($pedido);

            $cart = new Cart($pedido);
            $customer = new Customer((int) $cart->id_customer);
            $context = Context::getContext();
            $context->cart = $cart;
            $context->customer = $customer;
            $respuesta = intval($respuesta);
            $transaction = array(
                'transaction_id' => $_REQUEST["Ds_AuthorisationCode"]
            );
            if ($respuesta < 101) {
                // Compra válida
                $mailvars = array();
                $this->validateOrder($pedido, _PS_OS_PAYMENT_, $total, $this->displayName, NULL, $transaction, NULL, false, $customer->secure_key);
                if ($reg_estado == 1)
                    class_registro::removeByCartID($pedido);

            } elseif ($reg_estado == 1) {
                //se anota el pedido como no pagado
                class_registro::add($cart->id_customer, $pedido, $total, $respuesta);
            }
        }
    }

    public function hookPaymentReturn($params) {
        if (!$this->active)
            return;
        if (!empty($_REQUEST) && Configuration::get('SERVIRED_NOTIFICACION')===0){
            $this->module->processPN();
        }
        $this->context->smarty->assign(array(
            'this_path' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/'
        ));
        return $this->display(__FILE__, 'payment_return.tpl');
    }

    /**
     * Validate an order in database AND ADD EXTRA COST
     * Function called from a payment module
     *
     * @param integer $id_cart Value
     * @param integer $id_order_state Value
     * @param float $amountPaid Amount really paid by customer (in the default
      currency)
     * @param string $paymentMethod Payment method (eg. 'Credit cart')
     * @param string $message Message to attach to order
     */
    function validateOrder($id_cart, $id_order_state, $amountPaid, $paymentMethod = 'Unknown', $message = NULL, $extraVars = array(), $currency_special = NULL, $dont_touch_amount = false, $secure_key = false, Shop $shop = null) {
        global $cart;

        $cart = new Cart(intval($id_cart));
		$customer = new Customer((int) $cart->id_customer);
		$context = Context::getContext();
		$context->cart = $cart;
		$context->customer = $customer;
        $fee = $this->getCostValidated($cart);
        if ($fee > 0) {
			$cart->gift = 1;
			$cart->gift_message = $cart->gift_message . ' -' . $this->l('Recargo por pago con tarjeta') . ': ' . $fee;
			$cart->save();
			$currency = new Currency($currency_special ? (int)($currency_special) : (int)($cart->id_currency));
			$extraVars['{payment_fee}']=Tools::displayPrice($fee, $currency, false);
			$extraVars['{total_shipping}']=Tools::displayPrice($cart->getOrderTotal(true, Cart::ONLY_SHIPPING), $currency, false)." + ".Tools::displayPrice($fee, $currency, false);
			$extraVars['{total_paid}']= Tools::displayPrice($fee + $cart->getOrderTotal(true, Cart::BOTH), $currency, false);
            if(number_format($amountPaid, 2) == number_format($fee + $cart->getOrderTotal(true, Cart::BOTH), 2))
                parent::validateOrder($id_cart, $id_order_state, (float)Tools::ps_round((float)$cart->getOrderTotal(true, Cart::BOTH), 2), $paymentMethod, $message, $extraVars, $currency_special, $dont_touch_amount, $secure_key);
            else
                parent::validateOrder($id_cart, $id_order_state, (float)Tools::ps_round($amountPaid - $fee, 2), $paymentMethod, $message, $extraVars, $currency_special, $dont_touch_amount, $secure_key);

            $order = new Order($this->currentOrder);
            $order->total_shipping += $fee;
            //$order->total_wrapping += $fee;
            $order->total_paid_real = $amountPaid;

            $order->total_paid = floatval(Tools::ps_round(floatval($cart->getOrderTotal(true, 3) + $fee), 2));

            $order->save();
        } else {
            parent::validateOrder($id_cart, $id_order_state, $amountPaid, $paymentMethod, $message, $extraVars, $currency_special, $dont_touch_amount, $secure_key, $shop);
        }
    }
}
?>
