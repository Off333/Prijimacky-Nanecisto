<?php

//isset($_GET['debug']) ? $debug = true : $debug = false;
$debug = false;
//debug params
if($debug) {
    $_POST = [
        "hide"        => [false, false, false, true, true, true, true, false, false, false, true, false, false, false],
        "order"       => "Tel",
        "page"        => "1",
        "rowsPerPage" => "25",
        "sendEmail"   => true,
        "sort"        => "ASC",
        "type"        => "export",
        "exportType"  => "xls"
    ];
}

$RA = TRUE;
require_once "funkce.php";

if($_SERVER['REQUEST_METHOD'] == "POST" || $debug){
    if($_POST['type'] == "loadParam") {
        $user = $db->table('spravci')->where('User', '=', $_SESSION['user'])->select()->first();
        $FavList = $user['FavoriteListing'];
        if(!empty($FavList)) {
            echo $FavList;
        }
    } elseif($_POST['type'] == "deleteFile") {
    } else {
        $result = [];


        if(isset($_POST['type'])) {
            switch($_POST['type']) {
                case "changeRows":
                    if(isset($_POST['changedRows']) && $_POST['changedRows'] != "") {
                        foreach ($_POST['changedRows'] as $key => $value) {

                            $StateBefore = $db->table('prihlaseni')->where('Id', '=', $value['Id'])->select()->first();
                            
                            !isset($value['Paid']) ? $value['Paid'] = $StateBefore['Paid']: null;
                            !isset($value['LogedOut']) ? $value['LogedOut'] = $StateBefore['LogedOut']: null;
                            
                            if($value['Paid'] == 'Ano' && $value['LogedOut'] == 'Ne' && ($value['PaidDate'] == '' || $value['PaidDate'] == null || empty($value['PaidDate']))) {
                                $PaidDate=date("Y-m-d");
                            } elseif($value['Paid'] == 'Ne') {
                                $PaidDate=NULL;
                            } elseif($value['PaidDate'] !== '' && $value['PaidDate'] !== null && !empty($value['PaidDate'])) {
                                $PaidDate=$value['PaidDate'];
                            } elseif($value['LogedOut'] == 'Ano') {
                                $PaidDate=NULL;
                            }
                            $update = [];
                            $columns =      ['IP', 'FirstName', 'LastName', 'School', 'Email', 'Tel', 'VS', 'RegDateTime', 'Paid', 'PaidDate', 'MaxPaidDate', 'LogedOut', 'Comment'];
                            for ($column = 0; $column < 13; $column++) { 
                                $name = $columns[$column];
                                if(isset($value[$name])){
                                    if($name == "PaidDate") {
                                        $update[$name] = $PaidDate;
                                    } else if($name == "RegDateTime") {
                                        $update[$name] = $value['RegDate']." ".$value['RegTime'];
                                    } else {
                                        $update[$name] = $value[$name];
                                    }
                                }
                            }
                
                            $db->table('prihlaseni')->where('Id', '=', $value['Id'])->update($update);
                
                            if($_POST['sendEmail']) {
                                if($value['Paid'] == 'Ano' && $value['LogedOut'] == 'Ne' && ($value['PaidDate'] == '' || $value['PaidDate'] == null || empty($value['PaidDate'])) && $StateBefore['Paid'] != $value['Paid']) {
                                    $send = EmailMessage('pay', $db, $update);
                                    
                                } elseif($value['Paid'] == 'Ne' && $value['LogedOut'] == 'Ano' && $StateBefore['LogedOut'] != $value['LogedOut']) {
                                    $send = EmailMessage('logOut', $db, $update);

                                }
                            }
                        }
                    }
                    break;

                case "resetRows":
                    // pass 
                    break;
                
                case "saveParam":
                    $db->table('spravci')->where('User', '=', $_SESSION['user'])->update(['FavoriteListing' => json_encode($_POST)]);
                    break;

                case "logOutUnpaid":
                    $logOutNotPaidList = $db->table('prihlaseni')->where('akce_Id', '=', $eventInfo['Id'])->where('Paid', '=', 'Ne')->where('LogedOut', '=', 'Ne')->where('MaxPaidDate', '<', date("Y-m-d"))->select()->results();
                    foreach($logOutNotPaidList as $logOut) {
                        $_POST['sendEmail'] ? EmailMessage('logOut', $db, $logOut) : null;
                        $db->table('prihlaseni')->where('Id', '=', $logOut['Id'])->update(['LogedOut' => 'Ano']);
                    }
                    break;

                case "sendReminder":
                    $LogedInList = $db->table('prihlaseni')->where('Paid', '=', 'Ano')->where('LogedOut', '=', 'Ne')->where('akce_Id', '=', $eventInfo['Id'])->select()->results();
                    $success = EmailMessage('reminder', $db, $LogedInList);
                    break;
                                
                default:
                    // pass
                    break;
            }
        }

        isset($_POST['order']) ? $order = $_POST['order'] : $order= "Id";
        isset($_POST['sort']) ? $sort = $_POST['sort'] : $sort= "ASC";

        $hidenColumns = $_POST['hide'];

        $columns =      ["VS", "RegDate",          "RegTime",        "LastName", "FirstName", "Email", "Tel",     "School",         "MaxPaidDate",      "PaidDate",        "Comment",  "IP", "Paid",      "LogedOut"];
        $columnsCzech = ["VS", "Datum přihlášení", "Čas přihlášení", "Příjmení", "Jméno",     "Email", "Telefon", "Základní škola", "Termín zaplacení", "Datum zaplacení", "Poznámky", "IP", "Zaplaceno", "Odhlášen"];

        $result['sheetHeader'] = "<tr>";
        $showedLength = 0;
        for ($column = 0; $column < 14; $column++) { 
            if($hidenColumns[$column] == "true"){
                $showedLength++;
                $result['sheetHeader'] .= "<th><button class='btn btn-dark order changeBtn' data-loading=true type='button' name='tableHeader' data-sort='";
                $result['sheetHeader'] .= $sort;
                $result['sheetHeader'] .= "' data-order='";
                if($columns[$column] == "RegDate" || $columns[$column] == "RegTime") {
                    $result['sheetHeader'] .= "RegDateTime";
                } else {
                    $result['sheetHeader'] .= $columns[$column];
                }
                $result['sheetHeader'] .= "' data-selected='";
                if($order === $columns[$column] || (($columns[$column] == "RegDate" || $columns[$column] == "RegTime") && $order === "RegDateTime")) {
                    $result['sheetHeader'] .= "true";
                } else {
                    $result['sheetHeader'] .= "false";
                }
                $result['sheetHeader'] .= "'>";
                $result['sheetHeader'] .= $columnsCzech[$column];
                if($order === $columns[$column] || (($columns[$column] == "RegDate" || $columns[$column] == "RegTime") && $order === "RegDateTime")) {
                    if($sort == "DESC") {     
                        $result['sheetHeader'] .= "<img src='images/arrowDown.png' alt='šipka dolů'>";
                    } else {  
                        $result['sheetHeader'] .= "<img src='images/arrowUp.png' alt='šipka nahoru'>";
                    }
                }
                $result['sheetHeader'] .= "</button></th>";
            }
        }
        $result['sheetHeader'] .= "</tr>";

        isset($_POST['page']) ? $page = $_POST['page'] : $page = 1;
        isset($_POST['rowsPerPage']) ? $rowsPerPage = intval($_POST['rowsPerPage']) : $rowsPerPage = 25;
        isset($_POST['logedOut']) ? $logedOut = $_POST['logedOut'] : $logedOut = null;
        isset($_POST['paid']) ? $paid = $_POST['paid'] : $paid = null;
        isset($_POST['search']) ? $search = $_POST['search'] : $search = null;
        isset($_POST['monthRes']) ? $month = $_POST['monthRes'] : $month = null;
        if($paid == "Ne") {
            $monthColumn = "MaxPaidDate";
        } elseif($paid == "Ano") {
            $monthColumn = "PaidDate";
        } else {
            $monthColumn = "RegDateTime";
        }

        $selectedQuery = $db->table('prihlaseni')->where('akce_Id', '=', $eventInfo['Id']);
        $logedOut != null ? $selectedQuery->where('LogedOut', '=', $logedOut) : null;
        $paid != null ? $selectedQuery->where('Paid', '=', $paid) : null;
        $month != null ? $selectedQuery->where("MONTH(`$monthColumn`)", "=", $month) : null;
        if($search != null) {             
            $con = [['akce_Id', '=', 'NULL']];
            $conor = [];
            foreach ($columns as $key => $value) {
                if($value == "RegDate" || $value == "RegTime") {
                    $value = "RegDateTime";
                }
                $conor[] = [$value, "LIKE", "%".$search."%"];
            }
            $con['OR'] = $conor;  
            $selectedQuery->parseWhere($con);
        }
        $selectedQuery->orderBy($order, $sort);
        $rowsTemp = clone $selectedQuery;
        $selectedCount = $selectedQuery->select()->count();
        if($_POST['type'] == "showAllPag" || $rowsPerPage == "ALL") {
            $rowsTemp->paginate($selectedCount, $rowsPerPage, true, $page);
            $rowsPerPage = $selectedCount;
            $page = 1;
        } else {
            $rowsTemp->paginate($selectedCount, intval($rowsPerPage), false, $page);
        }
        
        $result['pagNav'] = $rowsTemp->link($rowsPerPage, $page);

        $rows = $rowsTemp->select()->results();

        $columnsType = ["number", "date", "time", "text", "text", "email", "tel", "text", "date", "date", "textarea", "text", "select", "select"];

        $result['sheetBody'] = "";


        foreach($rows as $row) {

            $row['RegDate'] = substr($row['RegDateTime'], 0, 10);
            $row['RegTime'] = substr($row['RegDateTime'], 11, 8);

            $rowHtml = "";

            $itemsStyle = ["", "", "", "", "", "", "", "", "", "", "", "", "", ""];

            isset($row['PaidDate']) ? $row['PaidDate'] = $row['PaidDate'] : $row['PaidDate'] = '';

            if($row['MaxPaidDate'] < date("Y-m-d") && $row['Paid'] == "Ne") {
                $itemsStyle[8] = 'bg-danger font-weight-bold';
            }

            $rowColor = '';
            if($row['LogedOut'] == "Ano" && $row['Paid'] == "Ne") {
                $rowColor = 'bg-danger';
            } elseif($row['LogedOut'] == "Ne" && $row['Paid'] == "Ano") {
                $rowColor = 'bg-success';
            } elseif($row['LogedOut'] == "Ano" && $row['Paid'] == "Ano") {
                $rowColor = 'bg-warning';
            }

            $rowHtml .= "<tr class={$rowColor}>";

            $rowHtml .= "<td class='d-none' hidden>";
            $rowHtml .= "<input class='rowId' data-changed=false value=".$row['Id'].">";
            $rowHtml .= "</td>";


            foreach ($columns as $index => $column) {
                if($hidenColumns[$index] == "true"){
                    $rowHtml .= "<td>";
                    $rowHtml .= "<p class='w-100 data-item text-nowrap text-truncate ";
                    $rowHtml .= $itemsStyle[$index];
                    $rowHtml .= "' style='max-width: ".(14-$showedLength+7)."em; text-overflow: clip;'>";
                    if($columnsType[$index] == "textarea") {

                        if(!isset($row['Comment']) || empty($row['Comment']) || $row['Comment'] == NULL) {
                            $PrintComment = '';
                            $PrintCommentOutside = '<i data-printRemove><small>Přidat poznámku</small></i>';
                        } else {
                            $PrintComment = $row['Comment'];
                            $PrintCommentOutside = $row['Comment'];
                        }
                        
                        $rowHtml .= "<textarea class='w-100 form-control d-none py-0 item-input' data-printRemove "; 
                        $rowHtml .= "data-row='".$row['Id']."' ";
                        $rowHtml .= "rows='1' ";
                        $rowHtml .= "name='".$column."' ";
                        $rowHtml .= "value='".$PrintComment."'";
                        $rowHtml .= ">";
                        $rowHtml .= $PrintComment;
                        $rowHtml .= "</textarea>";
                        
                        
                    } elseif($columnsType[$index] == "select") {
                        $optionYes = "";
                        $optionNo = "";
                        $row[$column] == "Ano" ? $optionYes = "selected" : $optionNo = "selected";

                        
                        $rowHtml .= "<select ";
                        $rowHtml .= "class='w-100 form-control d-none py-0 item-input' data-printRemove ";
                        $rowHtml .= "data-row='".$row['Id']."'";
                        $rowHtml .= "name='".$column."'";
                        $rowHtml .= ">";
                        $rowHtml .= "<option ";
                        $rowHtml .= "value='Ano'"; 
                        $rowHtml .= $optionYes;
                        $rowHtml .= ">";
                        $rowHtml .= "ano";
                        $rowHtml .= "</option>";
                        $rowHtml .= "<option ";
                        $rowHtml .= "value='Ne' ";
                        $rowHtml .= $optionNo;
                        $rowHtml .= ">";
                        $rowHtml .= "ne";
                        $rowHtml .= "</option>";
                        $rowHtml .= "</select>";

                    } else {

                        $rowHtml .= "<input class='w-100 form-control d-none py-0 item-input' data-printRemove ";
                        $rowHtml .= "name='".$column."' ";
                        $rowHtml .= "type='".$columnsType[$index]."' ";        
                        $rowHtml .= "value='".$row[$column]."' ";
                        $rowHtml .= "data-row='".$row['Id']."'";
                        $rowHtml .= ">";
                    }
                    $rowHtml .= "<label class='w-100 item-label'>";
                    if($columnsType[$index] == "date" && isset($row[$column]) && !empty($row[$column])) {
                        $rowHtml .= intval(substr($row[$column], 8, 2)).". ".dateMonth($row[$column])." ".substr($row[$column], 0, 4);
                    } else {
                        isset($PrintCommentOutside) ? $rowHtml .= $PrintCommentOutside : $rowHtml .= $row[$column];
                    }
                    $rowHtml .= "</label>";
                    $rowHtml .= "</p>";
                    $rowHtml .= "</td>";
                    if(isset($PrintCommentOutside)){ unset($PrintCommentOutside);}
                }
            }
            $rowHtml .= "</tr>";
            $result['sheetBody'] .= $rowHtml;
        }

        if($_POST['type'] == "export") {
            $nl = urlencode("\r\n"); //new line (line feed + that other thing)
            $nc = urlencode(","); //new cell
            $vt = "%09"; //vertical tab
            $lf = "%0A"; // line feed
            $columns = ["VS", "RegDate", "RegTime",        "LastName", "FirstName", "Email", "Tel",     "School",         "MaxPaidDate",      "PaidDate",        "Comment",  "IP", "Paid",      "LogedOut"];
            $columnsCzech = ["VS", "Datum přihlášení", "Čas přihlášení", "Příjmení", "Jméno",     "Email", "Telefon", "Základní škola", "Termín zaplacení", "Datum zaplacení", "Poznámky", "IP", "Zaplaceno", "Odhlášen"];
            
            if(isset($_POST['exportType']) && $_POST['exportType'] == "xls") {
                // hnusne ale funkcni
                $cell = urlencode(";");
                $line = $lf;
                $dataUri = "data:application/vnd.ms-excel";           
                $result['exportType'] = "xls";
                $dataUri .= ";charset=UTF-8";
                $dataUri .= ",";
                $dataUri .= $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ); //BOM
                $showedColumns = [urlencode("#")];
                for($index = 0; $index < 14; $index++) {
                    if($hidenColumns[$index] == "true"){
                        $showedColumns[] = rawurlencode('"'.$columnsCzech[$index].'"');
                    }
                }
                $dataUri .= implode($cell, $showedColumns).$line;
                foreach ($rows as $key => $value) {
                    $showedRow = [$key+1];
                    $value['RegDate'] = substr($value['RegDateTime'], 0, 10);
                    $value['RegTime'] = substr($value['RegDateTime'], 11, 8);
                    for($index = 0; $index < 14; $index++) {
                        if($hidenColumns[$index] == "true"){
                            if($value[$columns[$index]] != "") {
                                $showedRow[] = rawurlencode('"'.$value[$columns[$index]].'"');
                            } else {
                                $showedRow[] = rawurlencode('""');
                            }
                        }                    
                    }   
                    $dataUri .= implode($cell, $showedRow).$line; 
                } 

            } else {
                $cell = $nc;
                $line = $nl;                
                $dataUri = "data:text/csv";
                $result['exportType'] = "csv";
                $dataUri .= ";charset=UTF-8";
                $dataUri .= ",";
                $showedColumns = urlencode("#");
                for($index = 0; $index < 14; $index++) {
                    if($hidenColumns[$index] == "true"){
                        $showedColumns .= $cell.rawurlencode('"'.$columnsCzech[$index].'"');
                    }
                }
                $dataUri .= $showedColumns.$line;
                foreach ($rows as $key => $value) {
                    $showedRow = $key+1;
                    $value['RegDate'] = substr($value['RegDateTime'], 0, 10);
                    $value['RegTime'] = substr($value['RegDateTime'], 11, 8);
                    for($index = 0; $index < 14; $index++) {
                        if($hidenColumns[$index] == "true"){
                            $showedRow .= $cell.rawurlencode('"'.$value[$columns[$index]].'"');
                        }                    
                    }   
                    $dataUri .= $showedRow.$line; 
                }  
            }
            
              
            $result['download'] = $dataUri;
            /*
            $fileName = "Prjimacky_nanecisto_vypis_".date("d_m_Y_H_i_s").".xls";
            $filePath = "";
            $fp = fopen($filePath.$fileName, "w");
            
            print(is_writable("tmp/Prjimacky_nanecisto_vypis_".date("d_m_Y_H_i_s").".xls"));
            $columns = ["VS", "RegDate", "RegTime",        "LastName", "FirstName", "Email", "Tel",     "School",         "MaxPaidDate",      "PaidDate",        "Comment",  "IP", "Paid",      "LogedOut"];
            $showedColumns = ["#"];
            for($index = 0; $index < 14; $index++) {
                if($hidenColumns[$index] == "true"){
                    $showedColumns[] = $columns[$index];
                }
            }
            fputcsv($fp, $showedColumns);  
            foreach ($rows as $key => $value) {
                $showedRow = [$key+1];
                $value['RegDate'] = substr($row['RegDateTime'], 0, 10);
                $value['RegTime'] = substr($row['RegDateTime'], 11, 8);
                for($index = 0; $index < 14; $index++) {
                    if($hidenColumns[$index] == "true"){
                        $showedRow[] = $value[$columns[$index]];
                    }                    
                }   
                fputcsv($fp, $showedRow); 
            }            
            fclose($fp);

            $result['download'] = $filePath.$fileName;
            */
        }
        
        if($debug) {
            print($result['pagNav']);
            echo "<table>";
            print($result['sheetHeader']);
            print($result['sheetBody']);
            echo "</table>";
        } else {
            echo json_encode($result);
        }
    }
}
?>