<?php

/**
 * Reglas de acceso por nivel: 1 Administrador, 2 Gestor (con over opcional), 3 Gestor - Over, 4 Aerolinea.
 */
class Administracion_Model_Acceso
{
	private static function level()
	{
		return (string) Session::getInstance()->get('kt_login_level');
	}

	private static function puedeOver()
	{
		return (int) Session::getInstance()->get('user_puedeover') === 1;
	}

	public static function canPanel()
	{
		return self::level() === '1';
	}

	public static function canDashboard()
	{
		return in_array(self::level(), ['1', '2'], true);
	}

	public static function canDashboardOver()
	{
		$level = self::level();
		return $level === '1' || $level === '3' || ($level === '2' && self::puedeOver());
	}

	/**
	 * Ruta inicial del usuario segun su nivel.
	 */
	public static function home()
	{
		switch (self::level()) {
			case '1':
				return '/administracion/panel';
			case '2':
				return '/administracion/maletas';
			case '3':
				return Session::getInstance()->get('over') != null
					? '/administracion/maletas'
					: '/administracion/index/seleccionarover';
			default:
				return '/administracion/maletas';
		}
	}

	public static function redirectHome()
	{
		header('Location: ' . self::home());
		exit;
	}
}
