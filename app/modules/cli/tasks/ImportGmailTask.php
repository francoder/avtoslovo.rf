<?php

use Phalcon\Cli\Task,
    Molly\Models\CatalogLocalSupplier,
    Molly\Models\CatalogLocalItem,
    Molly\Models\CatalogLocalSteklo;

class ImportGmailTask extends Task
{
    public function mainAction()
    {
        echo "Start importing..." . PHP_EOL;

        $path = __DIR__ .  DIRECTORY_SEPARATOR . 'attachments' . DIRECTORY_SEPARATOR;

        $hostname = '{imap.gmail.com:993/imap/ssl}INBOX';
        $username = 'avtoslovorf@gmail.com'; # e.g somebody@gmail.com
        $password = 'avtoslovodotrf';

        $inbox = imap_open($hostname, $username, $password) or die('Cannot connect to Gmail: ' . imap_last_error());

        $emails = imap_search($inbox, 'ALL');

        /* useful only if the above search is set to 'ALL' */
        $max_emails = 10;

        /* if any emails found, iterate through each email */
        if ($emails) {

            echo "Downloading emails..." . PHP_EOL;

            if (!is_dir($path)) {
                mkdir($path);
            }

            $count = 1;

            /* put the newest emails on top */
            rsort($emails);

            /* for every email... */
            foreach ($emails as $email_number) {

                /* get information specific to this email */
                $overview = imap_fetch_overview($inbox, $email_number, 0);
                $headers = imap_headerinfo($inbox,$email_number);

                /* get mail message */
                $message = imap_fetchbody($inbox, $email_number, 2);

                /* get mail structure */
                $structure = imap_fetchstructure($inbox, $email_number);

                $attachments = array();

                /* if any attachments found... */
                if (isset($structure->parts) && count($structure->parts)) {
                    for ($i = 0; $i < count($structure->parts); $i++) {
                        $attachments[$i] = array(
                            'is_attachment' => false,
                            'filename' => '',
                            'name' => '',
                            'attachment' => ''
                        );

                        if ($structure->parts[$i]->ifdparameters) {
                            foreach ($structure->parts[$i]->dparameters as $object) {
                                if (strtolower($object->attribute) == 'filename') {
                                    $attachments[$i]['is_attachment'] = true;
                                    $attachments[$i]['filename'] = str_replace(["=?UTF-8?Q?", "?="], '', $object->value);
                                }
                            }
                        }

                        if ($structure->parts[$i]->ifparameters) {
                            foreach ($structure->parts[$i]->parameters as $object) {
                                if (strtolower($object->attribute) == 'name') {
                                    $attachments[$i]['is_attachment'] = true;
                                    $attachments[$i]['name'] = str_replace(["=?UTF-8?Q?", "?="], '', $object->value);
                                }
                            }
                        }

                        if ($attachments[$i]['is_attachment']) {
                            $attachments[$i]['attachment'] = imap_fetchbody($inbox, $email_number, $i + 1);

                            /* 4 = QUOTED-PRINTABLE encoding */
                            if ($structure->parts[$i]->encoding == 3) {
                                $attachments[$i]['attachment'] = base64_decode($attachments[$i]['attachment']);
                            } /* 3 = BASE64 encoding */
                            elseif ($structure->parts[$i]->encoding == 4) {
                                $attachments[$i]['attachment'] = quoted_printable_decode($attachments[$i]['attachment']);
                            }
                        }
                    }
                } else {
                    $attachments = array(
                        'is_attachment' => false,
                        'filename' => '',
                        'name' => '',
                        'attachment' => ''
                    );

                    if ($structure->ifdparameters) {
                        foreach ($structure->dparameters as $object) {
                            if (strtolower($object->attribute) == 'filename') {
                                $attachments['is_attachment'] = true;
                                $attachments['filename'] = str_replace(["=?UTF-8?Q?", "?="], '', $object->value);
                            }
                        }
                    }

                    if ($structure->ifparameters) {
                        foreach ($structure->parameters as $object) {
                            if (strtolower($object->attribute) == 'name') {
                                $attachments['is_attachment'] = true;
                                $attachments['name'] = str_replace(["=?UTF-8?Q?", "?="], '', $object->value);
                            }
                        }
                    }

                    if ($attachments['is_attachment']) {
                        $attachments['attachment'] = imap_fetchbody($inbox, $email_number, 1);

                        /* 4 = QUOTED-PRINTABLE encoding */
                        if ($structure->encoding == 3) {
                            $attachments['attachment'] = base64_decode($attachments['attachment']);
                        } /* 3 = BASE64 encoding */
                        elseif ($structure->encoding == 4) {
                            $attachments['attachment'] = quoted_printable_decode($attachments['attachment']);
                        }
                    }
                }

                /* iterate through each attachment and save it */
                if(is_array($attachments[0])) {
                    foreach ($attachments as $attachment) {
                        if ($attachment['is_attachment'] == 1) {
                            $filename = $attachment['filename'];
                            if (empty($filename)) $filename = $attachment['filename'];

                            if (empty($filename)) $filename = time() . ".dat";

                            /* prefix the email number to the filename in case two emails
                             * have the attachment with the same file name.
                             */
                            $ext = pathinfo($filename, PATHINFO_EXTENSION);
                            if(strpos($overview[0]->from, 'ivers') !== false) {
                                $fp = fopen($path . 'ivers' . "-" . $overview[0]->udate . '.'.$ext, "w+");
                            } else if(strpos($overview[0]->from, 'avtoopt') !== false) {
                                $fp = fopen($path . 'avtoopt' . "-" . $overview[0]->udate . '.'.$ext, "w+");
                            } else if(strpos($overview[0]->from, 'steklo') !== false) {
                                $fp = fopen($path . 'steklo' . "-" . $overview[0]->udate . '.' . $ext, "w+");
                            } else {
                                $fp = fopen($path . 'unknown' . "-" . $overview[0]->udate . '.'.$ext, "w+");
                            }
                            fwrite($fp, $attachment['attachment']);
                            fclose($fp);
                        }
                    }
                } else {
                    if ($attachments['is_attachment'] == 1) {
                        $filename = $attachments['filename'];
                        if (empty($filename)) $filename = $attachments['filename'];

                        if (empty($filename)) $filename = time() . ".dat";

                        /* prefix the email number to the filename in case two emails
                         * have the attachment with the same file name.
                         */
                        $ext = pathinfo($filename, PATHINFO_EXTENSION);
                        if(strpos($overview[0]->from, 'ivers') !== false) {
                            $fp = fopen($path . 'ivers' . "-" . $overview[0]->udate . '.'.$ext, "w+");
                        } else if(strpos($overview[0]->from, 'avtoopt') !== false) {
                            $fp = fopen($path . 'avtoopt' . "-" . $overview[0]->udate . '.'.$ext, "w+");
                        } else if(strpos($overview[0]->from, 'steklo') !== false) {
                            $fp = fopen($path . 'steklo' . "-" . $overview[0]->udate . '.'.$ext, "w+");
                        } else {
                            $fp = fopen($path . 'unknown' . "-" . $overview[0]->udate . '.'.$ext, "w+");
                        }
                        fwrite($fp, $attachments['attachment']);
                        fclose($fp);
                    }
                }

                if ($count++ >= $max_emails) break;
            }

        }

        imap_close($inbox);

        echo "Download complete" . PHP_EOL;

        set_time_limit(0);

        $archives = array_diff(scandir($path), array('..', '.'));

        foreach($archives AS $archive) {
            // Ищем zip архивы в директории
            if(pathinfo($archive, PATHINFO_EXTENSION) == 'zip') {
                echo "Unzipping " . $archive . " file..." . PHP_EOL;

                $zip = new \ZipArchive();
                $res = $zip->open($path . $archive);
                if($res === true) {
                    // Переименовываем файл прайса в название как у архива
                    for ($i = 0; $i < $zip->numFiles; $i++) {
                        if(strpos($archive, 'ivers') !== false) {
                            $ext = pathinfo($zip->getNameIndex($i), PATHINFO_EXTENSION);
                            $zip->renameIndex($i, pathinfo($archive, PATHINFO_FILENAME) . '.'.$ext);
                        }
                    }
                    $zip->close();
                    // Переоткрываем обработванный архив и распаковав его, удаляем
                    if($zip->open($path . $archive)) {
                        $zip->extractTo($path);
                        $zip->close();
                        unlink($path . $archive);
                    }
                } else {
                    echo 'Error while opening file ' . $archive .  ' with code: ' . $res;
                }
                echo "Unzipping complete" . PHP_EOL;
            }
        }

        $files = array_diff(scandir($path), array('..', '.'));
        $ivers = array();
        $avtoopt = array();
        $steklo = array();
        $prices = array();

        foreach($files AS $file) {
            if(strpos($file, 'ivers') !== false) {
                array_push($ivers, $file);
            } else if(strpos($file, 'avtoopt') !== false) {
                array_push($avtoopt, $file);
            } else if(strpos($file, 'steklo') !== false) {
                array_push($steklo, $file);
            }
        }
        if(!empty($ivers))  array_push($prices, max($ivers));
        if(!empty($avtoopt)) array_push($prices, max($avtoopt));
        if(!empty($steklo)) array_push($prices, max($steklo));

        foreach($prices AS $price) {
            if(strpos($price, 'ivers') !== false) {
                $localsupplier = CatalogLocalSupplier::findFirst(array(
                    "conditions" => "code = :code:",
                    "bind" => array(
                        "code" => 'IA',
                    ),
                ));
            } else if(strpos($price, 'avtoopt') !== false) {
                $localsupplier = CatalogLocalSupplier::findFirst(array(
                    "conditions" => "code = :code:",
                    "bind" => array(
                        "code" => 'AO',
                    ),
                ));
            } else if(strpos($price, 'steklo') !== false) {
                $localsupplier = CatalogLocalSupplier::findFirst(array(
                    "conditions" => "code = :code:",
                    "bind" => array(
                        "code" => 'SC', // TODO: сменить если измениться
                    ),
                ));
            } else {
                die('Unknown supplier');
            }

            $objPHPExcel = \PHPExcel_IOFactory::load($path.$price);

            $temporaryHash = md5($price);

            if($localsupplier->code == 'IA') {
                $fields = array(
                    "supplier" => array(
                        "value" => $localsupplier->code,
                    ),
                    "externalId" => array(
                        "cell" => "F",
                    ),
                    "hash" => array(
                        "value" => $temporaryHash,
                    ),
                    "oem" => array(
                        "cell" => "G",
                    ),
                    "brand" => array(
                        "cell" => "B",
                        "default" => "N/A",
                    ),
                    "title" => array(
                        "cell" => "D",
                    ),
                    "count" => array(
                        "cell" => "H",
                    ),
                    "price" => array(
                        "cell" => "I",
                    ),
                    "date" => array(
                        "value" => gmdate("d.m.Y", preg_replace("/[^0-9]/", '', $price))
                    ),
                );
            } else if($localsupplier->code == 'AO') {
                $fields = array(
                    "supplier" => array(
                        "value" => $localsupplier->code,
                    ),
                    "externalId" => array(
                        "cell" => "H",
                    ),
                    "hash" => array(
                        "value" => $temporaryHash,
                    ),
                    "oem" => array(
                        "cell" => "E",
                    ),
                    "brand" => array(
                        "cell" => "D",
                        "default" => "N/A",
                    ),
                    "title" => array(
                        "cell" => "B",
                    ),
                    "count" => array(
                        "cell" => "F",
                    ),
                    "price" => array(
                        "cell" => "J",
                    ),
                    "date" => array(
                        "value" => gmdate("d.m.Y", preg_replace("/[^0-9]/", '', $price))
                    ),
                );
            } else if ($localsupplier->code == 'SC') { // TODO: change SC
                $fields = array(
                    "supplier" => array(
                        "value" => $localsupplier->code,
                    ),
                    "externalId" => array(
                        "cell" => "C",
                    ),
                    "hash" => array(
                        "value" => $temporaryHash,
                    ),
                    "oem" => array(
                        "cell" => "C",
                    ),
                    "brand" => array(
                        "cell" => "A",
                        "default" => "N/A",
                    ),
                    "title" => array(
                        "cell" => "B",
                    ),
                    "count" => array(
                        "cell" => 'E',
                    ),
                    "price" => array(
                        "cell" => "K",
                    ),
                    "date" => array(
                        "value" => gmdate("d.m.Y", preg_replace("/[^0-9]/", '', $price))
                    ),
                );
            } else {
                die('Unknown supplier code');
            }

            echo "Deleting old data in " . $localsupplier->code . " supplier.." . PHP_EOL;

            $catalogLocalItem = CatalogLocalItem::find(array(
                "conditions" => "supplier = :supplier:",
                "bind" => array(
                    "supplier" => $localsupplier->code,
                ),
            ));
            $catalogLocalItem->delete();
            echo "Data was deleted" . PHP_EOL;

            echo "Start importing " . $price . " in database..." . PHP_EOL;

            foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
                foreach ($worksheet->getRowIterator() as $rkey => $row) {
                    if ($rkey <= 7)
                        continue;

                    $cellIterator = $row->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(false);

                    $rowData = array();
                    $cells = array();

                    foreach ($cellIterator as $ckey => $cell) {
                        $cells[$ckey] = $cell->getCalculatedValue();
                    }

                    foreach ($fields AS $code => $field) {
                        if (isset($field["value"]))
                            $rowData[$code] = $field["value"];
                        else {
                            if (trim($code) == "count" && trim($cells['H']) == "Есть") {
                                $rowData[$code] = 2;
                            } else if (trim($code) == "count" && strpos($cells['H'], 'пути') !== false) {
                                $rowData[$code] = 0;
                            } else if (trim($code) == "count" && strpos($cells['F'], '>20') !== false) {
                                $rowData[$code] = 20;
                            } else if (trim($code) == "oem" && $localsupplier->code != 'SC' || trim($code) == "externalId") {
                                $rowData[$code] = str_replace(array('-', ' '), '', str_replace(array(';', '/'), ',', trim($cells[$field["cell"]])));
                            } else if (trim($code) == "title" && $localsupplier->code == 'SC') {
                                $rowData[$code] = trim($cells[$field["cell"]]) . ' ' . $cells["D"];
                            } else if (trim($code) == "count" && $localsupplier->code == 'SC') {
                                $rowData[$code] = strpos($cells['E'], 'Более') !== false ? 5 : $cells['E'];
                                $rowData[$code] += strpos($cells['F'], 'Более') !== false ? 5 : $cells['F'];
                                $rowData[$code] += strpos($cells['G'], 'Более') !== false ? 5 : $cells['G'];
                                $rowData[$code] += strpos($cells['H'], 'Более') !== false ? 5 : $cells['H'];
                                $rowData[$code] += strpos($cells['I'], 'Более') !== false ? 5 : $cells['I'];
                                $rowData[$code] += strpos($cells['J'], 'Более') !== false ? 5 : $cells['J'];
                            } else if (trim($code) == "oem" && $localsupplier->code == 'SC') {
                                $catalogStekloItem = CatalogLocalSteklo::findFirst(array(
                                    "conditions" => "supplier = :supplier: AND externalId = :externalId:",
                                    "bind" => array(
                                        "supplier" => $localsupplier->code,
                                        "externalId" => $cells['C']
                                    ),
                                ));
                                $rowData[$code] = str_replace(array(';', '/'),',',$catalogStekloItem->oem);
                            } else {
                                $rowData[$code] = trim($cells[$field["cell"]]);
                            }
                            if (isset($field["default"]) AND !$rowData[$code]) {
                                $rowData[$code] = $field["default"];
                            }
                        }
                    }

                    $catalogItem = new CatalogLocalItem();

                    $catalogItem->assign($rowData);

                    $catalogItem->save();
                }
            }

            echo "Import complete" . PHP_EOL;
            unlink($path.$price);
        }
    }
}