<?php

	include ('system_prerequisite.php');
    
    function getContrastYIQ($hexcolor){
        $r = hexdec(substr($hexcolor,0,2));
        $g = hexdec(substr($hexcolor,2,2));
        $b = hexdec(substr($hexcolor,4,2));
        $yiq = (($r*299)+($g*587)+($b*114))/1000;
        return ($yiq >= 160) ? 'contrastDark' : 'contrastLight';
    }
    
    $color_palettes = array(
        "rgba(53,152,219,opacity)",
        "rgba(232,76,61,opacity)",
        "rgba(242,195,17,opacity)",
        "rgba(53,74,95,opacity)",
        "rgba(154,89,181,opacity)",
        "rgba(28,187,155,opacity)",
        "rgba(233,172,125,opacity)",
        "rgba(245,105,139,opacity)",
        "rgba(139,104,111,opacity)",
        "rgba(159,166,59,opacity)",
        "rgba(115,139,150,opacity)"
    );
    
	$sql = "
        SELECT
            CHART.ITEM_ID,
            CHART.CHART_PTYPE,
            CHART.CHART_BG_COLOR,
            REPLACE(CHART.chart_ssql, '||label||', '".$_POST['LABEL']."') AS CHART_SSQL
        FROM corrad214_engine.flc_chart CHART
        WHERE CHART.ITEM_ID = ".$_POST['ITEM_ID']."
    ";

	$rs = mysql_query($sql);
    $newChart = '';
    
    while ($row = mysql_fetch_assoc($rs)) {
        
        //Populate Json Data
        //===============================================================
            $labels = array();
            $datasets = array();
            $data = array();
            $cardData = '';
            $dt_thead = '';
            $dt_tbody = '';
                            
            $rsData = mysql_query($row["CHART_SSQL"]);
            $rowDataCount = -1;
            while ($rowData = mysql_fetch_assoc($rsData)) {
                $rowDataCount++;
                
                if(
                    $row["CHART_PTYPE"]=="Bar" ||
                    $row["CHART_PTYPE"]=="Line" ||
                    $row["CHART_PTYPE"]=="Radar"
                ) {
                    
                    array_push($labels, $rowData[array_keys($rowData)[0]]);
                    
                    $datasets = array();
                    for($i=1; $i<count($rowData); $i++) {
                        array_push($datasets,
                            '
                                {
                                    label: "'.array_keys($rowData)[$i].'",
                                    fillColor: "'.str_replace("opacity", "0.5", $color_palettes[$i-1]).'",
                                    strokeColor: "'.str_replace("opacity", "0.8", $color_palettes[$i-1]).'",
                                    highlightFill: "'.str_replace("opacity", "0.75", $color_palettes[$i-1]).'",
                                    highlightStroke: "'.str_replace("opacity", "1", $color_palettes[$i-1]).'",
                                    pointColor: "'.str_replace("opacity", "0.75", $color_palettes[$i-1]).'",
                                    pointStrokeColor: "#FFFFFF",
                                    pointHighlightFill: "#FFFFFF",
                                    pointHighlightStroke: "'.str_replace("opacity", "1", $color_palettes[$i-1]).'",
                                    data: []
                                }
                            '
                        );

                        $data[$i-1] .= ', '.$rowData[array_keys($rowData)[$i]];
                    }
                    
                }
                
                else if(
                    $row["CHART_PTYPE"]=="Doughnut" ||
                    $row["CHART_PTYPE"]=="Pie" ||
                    $row["CHART_PTYPE"]=="PolarArea"
                ) {
                    array_push($data,
                        '
                            {
                                value: '.$rowData[array_keys($rowData)[1]].',
                                color: "'.str_replace("opacity", "0.5", $color_palettes[$rowDataCount]).'",
                                highlight: "'.str_replace("opacity", "0.75", $color_palettes[$rowDataCount]).'",
                                label: "'.$rowData[array_keys($rowData)[0]].'"
                            }
                        '
                    );
                }
                
                else if($row["CHART_PTYPE"]=="Card") {
                    $cardData = $rowData[array_keys($rowData)[0]];
                }
                
                else if($row["CHART_PTYPE"]=="Tabular") {
                    $dt_thead = '<th></th>';
                    $td = '<td>'.$rowData[array_keys($rowData)[0]].'</td>';
                    for($i=1; $i<count($rowData); $i++) {
                        $dt_thead .= '<th>'.array_keys($rowData)[$i].'</th>';
                        $td .= '<td>'.$rowData[array_keys($rowData)[$i]].'</td>';
                    }
                    $dt_tbody .= '<tr>'.$td.'</tr>';
                }

            }
            
            if(
                    $row["CHART_PTYPE"]=="Bar" ||
                    $row["CHART_PTYPE"]=="Line" ||
                    $row["CHART_PTYPE"]=="Radar"
                ) {
                for($i=0; $i<count($data); $i++) { $data[$i] = preg_replace('/, /', '', $data[$i], 1); }
             }
        //===============================================================

        
        //Finalize Json Structure
        //===============================================================
            if(
                    $row["CHART_PTYPE"]=="Bar" ||
                    $row["CHART_PTYPE"]=="Line" ||
                    $row["CHART_PTYPE"]=="Radar"
            ) {
                for($i=0; $i<count($data); $i++) { $datasets[$i] = str_replace("data: []", "data: [".$data[$i]."]", $datasets[$i]); }
                $json = '{ labels: ["' . implode('", "', $labels) . '"], datasets: ['. implode(', ', $datasets) .'] }';
                $options = '{
                    scaleShowGridLines : false,
                    legendTemplate : "<table cellpadding=\"4\"><% for (var i=0; i<datasets.length; i++){%><tr><td valign=\"top\"><span style=\"background-color:<%=datasets[i].fillColor%>\"></span></td><td valign=\"top\"><%if(datasets[i].label){%><%=datasets[i].label%><%}%></td></tr><%}%></table>"
                }';
            }
            
            else if(
                    $row["CHART_PTYPE"]=="Doughnut" ||
                    $row["CHART_PTYPE"]=="Pie" ||
                    $row["CHART_PTYPE"]=="PolarArea"
            ) {
                $json = '[' . implode(', ', $data) . ']';
                $options = '{
                    scaleShowGridLines : false
                }';
            }
        //===============================================================
        
        
        //Populate Dashboard Items
        //===============================================================
            $newChart .= '
                <div class="title '.$row["CHART_PTYPE"].' '.getContrastYIQ(str_replace("#", "", $row["CHART_BG_COLOR"])).'"><a href="javascript:;" onclick="drillUp(this)">'.$_POST['ITEMTITLE'].'</a> &gt; '.$_POST['LABEL'].'</div>
                <div class="legend hidden" id="legend_level2_'.$row["ITEM_ID"].'"></div>
                <div class="canvas-container"><canvas id="canvas_level2_'.$row["ITEM_ID"].'" width="'.$_POST['CANVAS_W'].'" height="'.$_POST['CANVAS_H'].'"></canvas></div>
                
                <script>
                    var ctx = jQuery("#canvas_level2_'.$row["ITEM_ID"].'").get(0).getContext("2d");
                    var canvas_level2_'.$row["ITEM_ID"].' = new Chart(ctx).'.$row["CHART_PTYPE"].'('.$json.', '.$options.');
                    canvas[canvas.length] = canvas_level2_'.$row["ITEM_ID"].';
                    jQuery("#legend_level2_'.$row["ITEM_ID"].'").html(canvas_level2_'.$row["ITEM_ID"].'.generateLegend());
                    if(jQuery("#legend_level2_'.$row["ITEM_ID"].' tr").size()>1) jQuery("#legend_level2_'.$row["ITEM_ID"].'").removeClass("hidden");
                </script>
            ';
        //===============================================================
    }
    
    echo $newChart;
?>