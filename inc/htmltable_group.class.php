<?php
/*
 * @version $Id: HEADER 15930 2011-10-25 10:47:55Z jmd $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2012 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file: Damien Touraine
// Purpose of file:
// ----------------------------------------------------------------------

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}


class HTMLTable_Group extends HTMLTable_Base {

   private $name;
   private $value;
   private $new_headers = array();
   private $table;
   private $rows = array();

   protected function getSuperHeader($super_header_name) {
      return $this->table->getHeader($super_header_name);
   }

   function __construct(HTMLTable_ $table, $name, $value) {
      parent::__construct(false);
      $this->table = $table;
      $this->name = $name;
      $this->value = $value;
   }

   function getName() {
      return $this->name;
   }

   function getValue() {
      return $this->value;
   }

   function haveHeader(HTMLTable_Header $header) {
      foreach ($this->ordered_headers as $local_header) {
         if ($local_header == $header) {
            return true;
         }
      }
      return false;
   }

   function addHeader(HTMLTable_SuperHeader $super_header, $name,
                      $value, HTMLTable_Header $father = NULL) {
      try {
         if (isset($this->ordered_headers)) {
            throw new Exception(__('Implementation error : must define all headers before any row'));
         }
         return $this->appendHeader(new HTMLTable_SubHeader($super_header, $name, $value,
                                                     $father));
      } catch (Exception $e) {
         echo __FILE__." ".__LINE__." : ".$e->getMessage()."<br>\n";
      }
   }


   private function completeHeaders() {
      if (!isset($this->ordered_headers)) {
         $this->ordered_headers = array();

         foreach ($this->table->getHeaderOrder() as $header_name) {
            $header = $this->table->getHeader($header_name);
            $header_names = $this->getHeaderOrder($header_name);
            if (!$header_names) {
               $this->ordered_headers[] = $header;
            } else {
               $numberOfSubHeaders = count($header_names);
               $header->updateNumberOfSubHeader($numberOfSubHeaders);
               foreach($header_names as $sub_header_name) {
                  $header = $this->getHeader($header_name, $sub_header_name);
                  $this->ordered_headers[] = $header;
                  $header->numberOfSubHeaders = $numberOfSubHeaders;
               }
            }
         }
      }
   }


   function createRow() {
      $this->completeHeaders();
      $new_row = new HTMLTable_Row($this);
      $this->rows[] = $new_row;
      return $new_row;
   }


   function prepareDisplay() {
      foreach ($this->rows as $row) {
         $row->prepareDisplay();
      }
   }


   function display($totalNumberOfColumn) {
      if ($this->getNumberOfRows() > 0) {

         if (!empty($this->value)) {
            echo "\t<tr><th colspan='$totalNumberOfColumn'>".$this->value."</th></tr>\n";
         }

         echo "<tr>";
         foreach ($this->ordered_headers as $header) {
            if ($header instanceof HTMLTable_SubHeader) {
               $header->updateColSpan($header->numberOfSubHeaders);
            }
            echo "\t\t".$header->getTableHeader()."\n";
         }
         echo "</tr>";

         $previousNumberOfSubRows = 0;
         foreach ($this->rows as $row) {
            if (!$row->notEmpty()) {
               continue;
            }
            $currentNumberOfSubRow = $row->getNumberOfSubRows();
            if (($previousNumberOfSubRows * $currentNumberOfSubRow) > 1) {
               echo "<tr><td colspan='$totalNumberOfColumn'><hr></td></tr>";
            }
            $row->display($this->ordered_headers);
            $previousNumberOfSubRows = $currentNumberOfSubRow;
         }
      }
   }

   function getNumberOfRows() {
      $numberOfRows = 0;
      foreach ($this->rows as $row) {
         if ($row->notEmpty()) {
            $numberOfRows ++;
         }
      }
      return $numberOfRows;
   }
}
?>
