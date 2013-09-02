<?php

class ExtyDbUtility {

    private static $_prefix = array(
        'acl' => 'acl',
        'actions' => 'act',
        'groups' => 'grp',
        'users' => 'usr',
    );

    public static function Instance() {
        static $inst = null;
        if ($inst === null) {
            $inst = new ExtyDbUtility();
        }
        return $inst;
    }

    private function __construct() {
        
    }

    public static function getNewId($table) {
        $db = Yii::app()->db;
        $tableId = self::getTablePrefix($table) . 'id';
        $maxId = $db->createCommand()
                ->select("max($tableId) as id")
                ->from("$table")
                ->queryRow();
        $newId = $maxId['id'] + 1;
        return $newId;
    }

    public static function getTablePrefix($table) {
        return self::$_prefix[$table];
    }

    public static function updateTableActions($table = false) {
        if (!$table)
            $table = 'actions';
        $appActions = array();
        $modules = Yii::app()->metadata->getModules();
        foreach ($modules as $module) {
            $controllers = Yii::app()->metadata->getControllersActions($module);
            foreach ($controllers as $controller) {
                $controllerID = $controller['name'];
                $controllerID = substr($controllerID, 0, -10);
                foreach ($controller['actions'] as $action) {
                    if (strpos($action, Exty::AJAX_ACTION_SUFFIX))
                        continue;
                    $appActions[] = array(
                        'actmodule' => $module,
                        'actcontroller' => strtolower($controllerID),
                        'actaction' => strtolower($action)
                    );
                }
            }
        }

        $db = Yii::app()->db;
        $actions = $db->createCommand()
                ->select('actmodule, actcontroller,actaction')
                ->from($table)
                ->queryAll();
        $countAdded = 0;
        $countRemoved = 0;
//se le appActions non sono presenti nella tabella actions le aggiungo
        foreach ($appActions as $action) {
            if (in_array($action, $actions))
                continue;

//calcolo un nuovo id
            $action['actid'] = ExtyDbUtility::getNewId('actions');
            $action['actdesc'] = $action['actmodule'] . $action['actcontroller'] . $action['actaction'];
            $action['actparentid'] = 0;
            $action['actorder'] = 0;
            $action['actvisible'] = 0;
            $action['acticon'] = null;
//aggiungo il record nella tabella action
            $db->createCommand()
                    ->insert($table, $action);
            $countAdded++;
        }
//se nella tabella action ci sono action che non esistono più le elimino
        foreach ($actions as $action) {
            if (in_array($action, $appActions))
                continue;
            elseif ($action['actaction'] == '' || $action['actaction'] == null)
                continue;
            $db->createCommand()
                    ->delete($table, 'actmodule= :module and actcontroller= :controller and actaction= :action', array(
                        ':module' => $action['actmodule'],
                        ':controller' => $action['actcontroller'],
                        ':action' => $action['actaction'],
                            )
            );
            $countRemoved++;
        }
        $message = "Action aggiunte: $countAdded<br> Action rimosse: $countRemoved";
        return $message;
    }

    /**
     * Export given array to excel
     * @param array $data
     */
    public static function export(array $data, $type, $filename = '', array $headers=null) {
        set_time_limit(0);
        ini_set('memory_limit', '1000M');
        ini_set('display_errors', false);
        if ($filename == '')
            $filename = 'export';
        $filename.="_" . date('Ymd');
        if(!$headers)
            $headers = array_keys($data[0]);

        switch ($type) {
            case 'xls':
                $objPHPExcel = new PHPExcel();
                $objPHPExcel->setActiveSheetIndex(0);
                $sheet = $objPHPExcel->getActiveSheet();

                $bold = array('font' => array('bold' => true));

//header
                $col = 0;
                $row = 1;
                foreach ($headers as $value) {
                    $sheet->setCellValueByColumnAndRow($col, $row, $value);
                    $sheet->getStyleByColumnAndRow($col, $row)->applyFromArray($bold);
                    $col++;
                }
                foreach ($data as $key => $record) {
                    $col = 0;
                    $row++;
                    foreach ($record as $value) {
                        $sheet->setCellValueByColumnAndRow($col, $row, $value);
                        $col++;
                    }
                }
//autosize colonne
                $tmp = 0;
                foreach ($data[0] as $value) {
                    $sheet->getColumnDimensionByColumn($tmp)->setAutoSize(true);
                    $tmp++;
                }
                ob_end_clean();
                ob_start();

                header('Content-Type: application/vnd.ms-excel');
                header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                header('Cache-Control: max-age=0');
                $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
                $objWriter->save('php://output');
                break;
            case 'pdf':
                $pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                $output = '<table border="1" cellpadding="2">';
                $output .= '<tr>';
                foreach ($headers as $header) {
                    $output .= '<th><b>' . $header . '</b></th>';
                }
                $output .= '</tr>';
                foreach ($data as $record) {
                    $output .= '<tr>';
                    foreach ($record as $v)
                        $output .= '<td>' . trim($v) . '</td>';
                    $output .= '</tr>';
                }
                $output .= '</table>';
//                $pdf = Yii::createComponent('application.vendors.tcpdf.ETcPdf', 'P', 'cm', 'A4', true, 'UTF-8');
                $pdf->SetMargins(10, 10, 10);
                $pdf->SetAutoPageBreak(true, 10);
                $pdf->SetFont('helvetica', '', 8);
                $pdf->AddPage();
                $pdf->writeHTML($output, true, false, true, false, '');
                $pdf->lastPage();
                $pdf->Output($filename . '.pdf', 'D');
                break;
        }
    }

}