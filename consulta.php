<?php 
set_time_limit(1000);
// conexion mysql
function insertBudget($fecha,$budget,$idWarehouse,$dia){
    $servername = "localhost";
$database = "budget_calculo";
$username = "root";
$password = "";
// Create connection
$conn = mysqli_connect($servername, $username, $password, $database);
// Check connection
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }else{
                $sql = "CALL insercion_($idWarehouse, '$dia', $budget,'$fecha')";
        if (mysqli_query($conn, $sql)) {
           // echo "New record created successfully";
           //echo $sql.'<br>';
        } else {
            echo "Error: " . $sql . "<br>" . mysqli_error($conn);
        }
        mysqli_close($conn);
    }
}
//conexion a la base
$dbconn = pg_connect("host=odoo12i7.cainoaw1vj7x.us-east-1.rds.amazonaws.com dbname=prod1 user=bi_index password=1nd3Xx.7!")
    or die('No se ha podido conectar: ' . pg_last_error());
//-*------------------------------------------//
//FUNCIONES DIAS 
$date =  date("Y-m-d");
$lastDay= date("t", strtotime($date));
function diaSemana($fecha){
    $dias = array('sunday','monday','tuesday','wednesday','thursday','friday','saturday');
   //  $dia = $dias[(date('N', strtotime($fecha))) - 1];
   $dia= date('l', strtotime($fecha));
     return $dia;
}
function cuenta_dias($mes,$anio,$numero_dia)
{
    $count=0;
    $dias_mes=cal_days_in_month(CAL_GREGORIAN, $mes, $anio);
    for($i=1;$i<=$dias_mes;$i++)
    if(date('N',strtotime($anio.'-'.$mes.'-'.$i))==$numero_dia)
    $count++;
    return $count;
}
// Siendo : 1-Lunes,2-Martes,3-miercoles,4-jueves,5-viernes,6-sabado,7-domingo
//echo cuenta_dias(06,2021,5).'<br> '.date("m").'<br>';

function cal_days_in_year($year,$day){
    $days=0; 
    for($month=1;$month<= date("n") ;$month++){ 
       // $days = $days + cal_days_in_month(CAL_GREGORIAN,$month,$year);
       if ($month<10) {
      $days+=  cuenta_dias('0'.$month,$year,$day); 
       }else{
       $days+= cuenta_dias($month,$year,$day);           
       }

     }
       return $days;
    }

//--------------------------------------------//
$dias = array('sunday','monday','tuesday','wednesday','thursday','friday','saturday');
$sucursales= array(43,37,42,38,44,14,40,13,7,9,41,8,1,10,48,34,11,6,39,47,12,4);
$sumaSucursales =array();
$totalEmpresa=0;
$year=date("Y");
$datosSucursal=array();
$dato=array();
$datoLS= array();
$cont=0;
for ($i=0; $i <count($sucursales); $i++) { // for sucursales
    //$sucursales[$i]
    $sumaDomingo=0;
$sumaLunes=0;
$sumaMartes=0;
$sumaMiercoles=0;
$sumaJueves=0;
$sumaViernes=0;
$sumaSabado=0;
//-----------------------------------------//
    $queryEncabezado = "SELECT ped.confirmation_date,
    ped.name,
    ped.state,
    ped.warehouse_id,
    alm.name as almacen,
    ped.amount_untaxed,
    ped.amount_tax,
    ped.amount_total,
    TO_CHAR(ped.confirmation_date, 'day') AS dia
    from public.sale_order as ped
    left join public.stock_warehouse as alm on ped.warehouse_id=alm.id
    WHERE ped.state NOT IN ('draft','sent','cancel','done') AND ped.company_id=1
    AND (ped.confirmation_date-INTERVAL '6 hours')>='2021-01-01 00:00:00' AND ped.warehouse_id=$sucursales[$i]
    ";
//echo $queryEncabezado;
    $resultMontos = pg_query($dbconn,$queryEncabezado) or die('La consulta fallo: ' . pg_last_error());
    $sucursal = pg_fetch_all($resultMontos);
 foreach($sucursal as $value){
       if ($value['dia']=='sunday   ') {
           $sumaDomingo=$sumaDomingo+$value['amount_untaxed'];
       }elseif ($value['dia']=='monday   ') {
        $sumaLunes=$sumaLunes+$value['amount_untaxed'];
            }elseif ($value['dia']=='tuesday  ') {
                $sumaMartes=$sumaMartes+$value['amount_untaxed'];
                }elseif ($value['dia']=='wednesday') {
                    $sumaMiercoles=$sumaMiercoles+$value['amount_untaxed'];
                    }elseif ($value['dia']=='thursday ') {
                        $sumaJueves=$sumaJueves+$value['amount_untaxed'];
                        }elseif ($value['dia']=='friday   ') {
                            $sumaViernes=$sumaViernes+$value['amount_untaxed'];
                            }else{
                                $sumaSabado=$sumaSabado+$value['amount_untaxed'];
                                }

    }
    
    $totalSucursal=$sumaDomingo+$sumaLunes+$sumaMartes+$sumaMiercoles+$sumaJueves+$sumaViernes+$sumaSabado;
    $totalEmpresa=$totalEmpresa+$totalSucursal;
    echo 'Sucursal: '.$sucursales[$i];
    echo '<table style="border: 1px solid black;"><tr><td>Domingo</td><td>Lunes</td><td>Martes</td><td>Miercoles</td><td>Jueves</td><td>Viernes</td><td>Sabadp</td></tr>';
    echo "<tr><td>$sumaDomingo</td><td>$sumaLunes</td><td>$sumaMartes</td><td>$sumaMiercoles</td><td>$sumaJueves</td><td>$sumaViernes</td><td>$sumaSabado</td></tr>";
    echo "<tr><td>Total</td><td colspan='6'>$totalSucursal</td></tr></table>";
    $dato[0]=array("sucursal"=>$sucursales[$i],"monto"=>$sumaDomingo,"dia"=>"sunday");
    $dato[1]=array("sucursal"=>$sucursales[$i],"monto"=>$sumaLunes,"dia"=>"monday");
    $dato[2]=array("sucursal"=>$sucursales[$i],"monto"=>$sumaMartes,"dia"=>"tuesday");
    $dato[3]=array("sucursal"=>$sucursales[$i],"monto"=>$sumaMiercoles,"dia"=>"wednesday");
    $dato[4]=array("sucursal"=>$sucursales[$i],"monto"=>$sumaJueves,"dia"=>"thursday");
    $dato[5]=array("sucursal"=>$sucursales[$i],"monto"=>$sumaViernes,"dia"=>"friday");
    $dato[6]=array("sucursal"=>$sucursales[$i],"monto"=>$sumaSabado,"dia"=>"saturday");
    $datoLS[$i]=$dato;
    //Sacar promedio 
    $sumaPromedio=0;
    $promedioLunes=0;
    $promedioMartes=0;
    $promedioMiercoles=0;
    $promedioJueves=0;
    $promedioViernes=0;
    $promedioSabados=0;
    $promedioDomingos=0;
    for ($day=1; $day <=7 ; $day++) { 
    
        if ($day==1) {
            $promedioLunes=$sumaLunes/cal_days_in_year($year,$day);
            $sumaPromedio+=$sumaLunes/cal_days_in_year($year,$day);
         //   echo 'Promedio de montos para dia Lunes: '.$sumaLunes/cal_days_in_year($year,$day).'<br>';
        }elseif ($day==2) {
            $promedioMartes=$sumaMartes/cal_days_in_year($year,$day);
            $sumaPromedio+=$sumaMartes/cal_days_in_year($year,$day);
         //   echo 'Promedio de montos para dia Martes: '.$sumaMartes/cal_days_in_year($year,$day).'<br>';
        }elseif ($day==3) {
            $promedioMiercoles=$sumaMiercoles/cal_days_in_year($year,$day);
            $sumaPromedio+=$sumaMiercoles/cal_days_in_year($year,$day);
         //   echo 'Promedio de montos para dia Miercoles: '.$sumaMiercoles/cal_days_in_year($year,$day).'<br>';
        }elseif ($day==4) {
            $promedioJueves=$sumaJueves/cal_days_in_year($year,$day);
            $sumaPromedio+=$sumaJueves/cal_days_in_year($year,$day);
        //   echo 'Promedio de montos para dia Jueves: '.$sumaJueves/cal_days_in_year($year,$day).'<br>';
        }elseif ($day==5) {
            $promedioViernes=$sumaViernes/cal_days_in_year($year,$day);
            $sumaPromedio+=$sumaViernes/cal_days_in_year($year,$day);
         //   echo 'Promedio de montos para dia Viernes: '.$sumaViernes/cal_days_in_year($year,$day).'<br>';
        }elseif ($day==6) {
            $promedioSabados=$sumaSabado/cal_days_in_year($year,$day);
            $sumaPromedio+=$sumaSabado/cal_days_in_year($year,$day);
        //    echo 'Promedio de montos para dia Sabado: '.$sumaSabado/cal_days_in_year($year,$day).'<br>';
        }else{
            $promedioDomingos=$sumaDomingo/cal_days_in_year($year,$day);
            $sumaPromedio+=$sumaDomingo/cal_days_in_year($year,$day);
         //   echo 'Promedio de montos para dia Domingo: '.$sumaDomingo/cal_days_in_year($year,$day).'<br>';
        }
    }
    
    //------------------------------------------------------//
    //Porcentaje dias
    
    $porcentajeLunes=0;
    $porcentajeMartes=0;
    $porcentajeMiercoles=0;
    $porcentajeJueves=0;
    $porcentajeViernes=0;
    $porcentajeSabado=0;
    $porcentajeDomingo=0;
    //-------------------calculo porcentaje ----------------------------//
    //echo $sumaPromedio;
   /* $porcentajeLunes=($sumaLunes/$totalSucursal)*100;
    $porcentajeMartes=($sumaMartes/$totalSucursal)*100;
    $porcentajeMiercoles=($sumaMiercoles/$totalSucursal)*100;
    $porcentajeJueves=($sumaJueves/$totalSucursal)*100;
    $porcentajeViernes=($sumaViernes/$totalSucursal)*100;
    $porcentajeSabado=($sumaSabado/$totalSucursal)*100;
    $porcentajeDomingo=($sumaDomingo/$totalSucursal)*100;*/
    //--------------------------------------------------------------//
    $porcentajeLunes=($promedioLunes/$sumaPromedio)*100;
    $porcentajeMartes=($promedioMartes/$sumaPromedio)*100;
    $porcentajeMiercoles=($promedioMiercoles/$sumaPromedio)*100;
    $porcentajeJueves=($promedioJueves/$sumaPromedio)*100;
    $porcentajeViernes=($promedioViernes/$sumaPromedio)*100;
    $porcentajeSabado=($promedioSabados/$sumaPromedio)*100;
    $porcentajeDomingo=($promedioDomingos/$sumaPromedio)*100;
    //--------------------------------------------------------------//
    $sumaPorcentajes=$porcentajeLunes+$porcentajeMartes+$porcentajeMiercoles+$porcentajeJueves+$porcentajeViernes+$porcentajeSabado+$porcentajeDomingo;
    //------------------------------------------------------//
    echo 'Promedio de montos para dia Lunes: '.$promedioLunes.', porcentaje:'.$porcentajeLunes.'<br>';
    echo 'Promedio de montos para dia Martes: '.$promedioMartes.', porcentaje:'.$porcentajeMartes.'<br>';
    echo 'Promedio de montos para dia Miercoles: '.$promedioMiercoles.', porcentaje:'.$porcentajeMiercoles.'<br>';
    echo 'Promedio de montos para dia Jueves: '.$promedioJueves.', porcentaje:'.$porcentajeJueves.'<br>';
    echo 'Promedio de montos para dia Viernes: '.$promedioViernes.', porcentaje:'.$porcentajeViernes.'<br>';
    echo 'Promedio de montos para dia Sabado: '.$promedioSabados.', porcentaje:'.$porcentajeSabado.'<br>';
    echo 'Promedio de montos para dia Domingo: '.$promedioDomingos.', porcentaje:'.$porcentajeDomingo.'<br>';
    echo '----------------------------------------------------------------------------------<br>';
    echo 'Suma Promedio: $'.$sumaPromedio.'<br>';
    echo 'Suma Porcentaje: '.$sumaPorcentajes.'%<br>';
    echo '----------------------------------------------------------------------------------<br>';

    //----------- BUDGET POR SUCURSAL ------------------
    require_once 'Classes/PHPExcel.php';
    $archivo = "Budget.xlsx";
    $inputFileType = PHPExcel_IOFactory::identify($archivo);
    $objReader = PHPExcel_IOFactory::createReader($inputFileType);
    $objPHPExcel = $objReader->load($archivo);
    $sheet = $objPHPExcel->getSheet(0); 
    $highestRow = $sheet->getHighestRow(); 
    $highestColumn = $sheet->getHighestColumn();
    //------------------------------------------------------//
    $num=0;
    for ($row = 2; $row <= $highestRow; $row++){
         $num++;
    $sucursalCELL= $sheet->getCell("A".$row)->getValue();
    if ($sucursalCELL==$sucursales[$i]) {
       $budgetSucursal=$sheet->getCell("C".$row)->getValue();
        //----------------------------------------------------------------------------------//
         /*   $participacionBudgetL=(intval($porcentajeLunes)/100)*$budgetSucursal;
            $participacionBudgetM=(intval($porcentajeMartes)/100)*$budgetSucursal;
            $participacionBudgetMi=(intval($porcentajeMiercoles)/100)*$budgetSucursal;
            $participacionBudgetJ=(intval($porcentajeJueves)/100)*$budgetSucursal;
            $participacionBudgetV=(intval($porcentajeViernes)/100)*$budgetSucursal;
            $participacionBudgetS=(intval($porcentajeSabado)/100)*$budgetSucursal;
            $participacionBudgetD=(intval($porcentajeDomingo)/100)*$budgetSucursal;*/
        //-----------------------------------------------------------------------------------//
        $participacionBudgetL=(($porcentajeLunes)/100)*$budgetSucursal;
        $participacionBudgetM=(($porcentajeMartes)/100)*$budgetSucursal;
        $participacionBudgetMi=(($porcentajeMiercoles)/100)*$budgetSucursal;
        $participacionBudgetJ=(($porcentajeJueves)/100)*$budgetSucursal;
        $participacionBudgetV=(($porcentajeViernes)/100)*$budgetSucursal;
        $participacionBudgetS=(($porcentajeSabado)/100)*$budgetSucursal;
        $participacionBudgetD=(($porcentajeDomingo)/100)*$budgetSucursal;
    }    
  
   }
   $year=date('Y');
   $mes=date('m');
   $numDiaL=0;
   $numDiaM=0;
   $numDiaMi=0;
   $numDiaJ=0;
   $numDiaV=0;
   $numDiaS=0;
   $numDiaD=0;
   echo '<table style="border: 1px solid black;">
   <tr><td>Dias</td><td>Lunes</td><td>Martes</td><td>Miercoles</td><td>Jueves</td><td>Viernes</td><td>Sabadp</td><td>Domingo</td></tr>';
   for ($day=1; $day <=7 ; $day++) { 
    
        if ($day==1) {
            $participacionLunes=$participacionBudgetL/cuenta_dias($mes,$year,$day);
            $numDiaL=cuenta_dias($mes,$year,$day);
        }elseif ($day==2) {
            $participacionMartes=$participacionBudgetM/cuenta_dias($mes,$year,$day);
            $numDiaM=cuenta_dias($mes,$year,$day);
        }elseif ($day==3) {
            $participacionMiercoles=$participacionBudgetMi/cuenta_dias($mes,$year,$day);
            $numDiaMi=cuenta_dias($mes,$year,$day);
        }elseif ($day==4) {
            $numDiaJ=cuenta_dias($mes,$year,$day);
            $participacionJueves=$participacionBudgetJ/cuenta_dias($mes,$year,$day);
        }elseif ($day==5) {
            $participacionViernes=$participacionBudgetV/cuenta_dias($mes,$year,$day);
            $numDiaV=cuenta_dias($mes,$year,$day);
        }elseif ($day==6) {
            $participacionSabados=$participacionBudgetS/cuenta_dias($mes,$year,$day);
            $numDiaS=cuenta_dias($mes,$year,$day);
        }else{
            $participacionDomingos=$participacionBudgetD/cuenta_dias($mes,$year,$day);
            $numDiaD=cuenta_dias($mes,$year,$day);
        }
    }
//echo $participacionJueves;
echo 'Budget Sucursal: $'.$budgetSucursal;
 $totalBudget=$participacionBudgetL+$participacionBudgetM+$participacionBudgetMi+$participacionBudgetJ+$participacionBudgetV+$participacionBudgetS+$participacionBudgetD;
 echo "<tr><td>Numero de dias</td>";
 echo "<td>$numDiaL</td><td>$numDiaM</td><td>$numDiaMi</td><td>$numDiaJ</td><td>$numDiaV</td><td>$numDiaS</td><td>$numDiaD</td>";       
 echo '</tr>';
 echo "<tr><td>Participacion Budget</td><td>$participacionBudgetL</td><td>$$participacionBudgetM</td><td>$$participacionBudgetMi</td><td>$$participacionBudgetJ</td><td>$$participacionBudgetV</td><td>$$participacionBudgetS</td><td>$$participacionBudgetD</td></tr>";
 echo "<tr><td>Participacion Diaria</td><td>$participacionLunes</td><td>$$participacionMartes</td><td>$$participacionMiercoles</td><td>$$participacionJueves</td><td>$$participacionViernes</td><td>$$participacionSabados</td><td>$$participacionDomingos</td></tr>";
 echo "<tr><td>Total</td><td colspan='6'>$totalBudget</td></tr></table>";

 //--------------participacion mensual -----------------------------------//
  $mes=date('m');
  $anio=date('Y');
  echo "<table  border='1'>
  <tr><td>FECHA</td><td>DIA</td><td>BUDGET_CALCULO</td><td>ID WAREHOUSE</td></tr>";
 for ($dy=1; $dy <= $lastDay; $dy++) { 
  /* if ($dy<10) {
        $fecha="0$dy/$mes/$year"; 
     }else{
        $fecha="$dy/$mes/$year"; 
     }*/
    if ($dy<10) {
        $fecha="$year/$mes/0$dy"; 
     }else{
        $fecha="$year/$mes/$dy"; 
     }
     
     if ($dy<10) {
        $dia=diaSemana("$mes/0$dy/$year");
     }else{
        $dia=diaSemana("$mes/$dy/$year");
     }
     
     $montoDia=0;
     if ($dia=='Sunday') {
         $montoDia=$participacionDomingos;     
     }elseif ($dia=='Monday') {
        $montoDia=$participacionLunes;     
     }elseif ($dia=='Tuesday') {
        $montoDia=$participacionMartes;     
     }elseif ($dia=='Wednesday') {
        $montoDia=$participacionMiercoles;     
     }elseif ($dia=='Thursday') {
        $montoDia=$participacionJueves;     
     }elseif ($dia=='Friday') {
        $montoDia=$participacionViernes;     
     }else{
        $montoDia=$participacionSabados;  
     }
    echo "<tr><td>$fecha</td><td>$dia</td><td>$montoDia</td><td>$sucursales[$i]</td></tr>";
    
    insertBudget($fecha,$montoDia,$sucursales[$i],$dia);
 }
 echo '</table>';


}//end sucursales
//var_dump($dato);
//for ($h=0; $h < $cont ; $h++) echo 1dato[1h];
echo('<pre>');
//print_r($datoLS);
echo('</pre>');
echo 'TOTAL EMPRESA: $'.$totalEmpresa.'<br>';

  //  echo cal_days_in_year(2021,1).'<br>';
    for ($day=1; $day <=7 ; $day++) { 
    
            if ($day==1) {
                //--------------------------------------------------------//
                echo 'Cantidad de dia Lunes: '.cal_days_in_year($year,$day).'<br>';
                //--------------------------------------------------------//
            }elseif ($day==2) {
                //--------------------------------------------------------//
                echo 'Cantidad de dia Martes: '.cal_days_in_year($year,$day).'<br>';
                //--------------------------------------------------------//
            }elseif ($day==3) {
                //--------------------------------------------------------//
                echo 'Cantidad de dia Miercoles: '.cal_days_in_year($year,$day).'<br>';
                //--------------------------------------------------------//
            }elseif ($day==4) {
                //--------------------------------------------------------//
                echo 'Cantidad de dia Jueves: '.cal_days_in_year($year,$day).'<br>';
                //--------------------------------------------------------//
            }elseif ($day==5) {
                //--------------------------------------------------------//
                echo 'Cantidad de dia Viernes: '.cal_days_in_year($year,$day).'<br>';
                //--------------------------------------------------------//
            }elseif ($day==6) {
                //--------------------------------------------------------//
                echo 'Cantidad de dia Sabados: '.cal_days_in_year($year,$day).'<br>';
                //--------------------------------------------------------//
            }else{
                //--------------------------------------------------------//
                echo 'Cantidad de dia Domingos: '.cal_days_in_year($year,$day).'<br>';
                //--------------------------------------------------------//
            }
           
            echo diaSemana('06/01/2021');
       
    }
    
?>
