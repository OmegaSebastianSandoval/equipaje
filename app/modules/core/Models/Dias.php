<?php
class Core_Model_Dias{
		public function primerDiaMes($fecha)
		{
			$fecha= strtotime($fecha); //Recibimos la fecha y la convertimos a tipo fecha
			$d = date("d",$fecha); //Obtenemos el dia
			$m = date("m",$fecha); //Obtenemos el mes
			$Y = date("Y",$fecha); //Obtenemos el año
			$primerDia = date("d-m-Y", mktime(0, 0, 0,$m, $d-$d +1,$Y)); //Obtenemos el primer dia del mes

			return $primerDia; //Regresamos el valor obtenido
		}

		public function ultimoDiaMes($fecha)
		{
			$fecha= strtotime($fecha); //Recibimos la fecha y la convertimos a tipo fecha
			$d = date("d",$fecha); //Obtenemos el dia
			$m = date("m",$fecha); //Obtenemos el mes
			$Y = date("Y",$fecha); //Obtenemos el año
			$ultimoDia = date("d-m-Y", mktime(0, 0, 0,$m+1,$d-$d,$Y)); //Obtenemos el ultimo dia del mes

			return $ultimoDia; //Regresamos el valor obtenido
		}
}