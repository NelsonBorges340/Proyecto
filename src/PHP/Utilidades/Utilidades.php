<?php

class Utilidades{

    function separarFecha($Fecha)
    {

        $partes = explode("-", $Fecha);
        $anio = $partes[0];
        $mes = $partes[1];
        $dia = $partes[2];


    $meses = 
    [
        "00" => "ERROR",
        "01" => "Enero", 
        "02" => "Febrero", 
        "03" => "Marzo",
        "04" => "Abril",
        "05" => "Mayo", 
        "06" => "Junio",
        "07" => "Julio", 
        "08" => "Agosto", 
        "09" => "Septiembre",
        "10" => "Octubre", 
        "11" => "Noviembre", 
        "12" => "Diciembre"
    ];

    $mesPalabra = $meses[$mes];


    return $Datos = 
    [   
        "Anio" => $anio,
        "Mes" =>$mes,
        "Dia" =>$dia,
        "MesP" =>$mesPalabra
    ];
    }





}

?>