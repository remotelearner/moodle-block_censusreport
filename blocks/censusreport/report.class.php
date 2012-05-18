<?php

// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Generic report class.
 *
 * @package   blocks-censusreport
 * @author    Justin Filip <jfilip@remote-learner.net>
 * @copyright 2009 Remote Learner - http://www.remote-learner.net/
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


class report {

    public $id;              // INT - Table identifier.
    public $title;           // STRING - The title for this report.
    public $type;            // STRING - The type of this report.
    public $table;           // OBJECT - The table object.
    public $columns;         // ARRAY - An array of strings.
    public $headers;         // ARRAY - An array of strings.
    public $footers;         // ARRAY - An array of strings.
    public $align;           // ARRAY - An array of strings.
    public $sortable;        // ARRAY - An array of bools.
    public $wrap;            // ARRAY - An array of bools.
    public $defsort;         // STRING - A column to sort by default.
    public $defdir;          // STRING - The direction to sort the default column by.
    public $data;            // ARRAY - An array of table data.
    public $numrecs;         // INT - The total number of results found.
    public $baseurl;         // STRING - The base URL pointing to this report.
    public $pagerl;          // STRING - The paging URL for this report.
    public $sort;            // STRING - The column to sort by.
    public $dir;             // STRING - The direction of sorting.
    public $page;            // INT - The page number being displayed.
    public $perpage;         // INT - The number of rows per page.
    public $search;          // STRING - A search query.
    public $alpha;           // STRING - A character to do initial filtering with
    public $fileformats;     // ARRAY - An array of strings for valid file formats.
    public $top;             // STRING - A top text block
    public $bottom;          // STRING - A bottom text block
    public $signatureline;   // BOOL - Whether to display a signature line
    public $dateline;        // BOOL - Whether to display a date line


    /**
     * Constructor.
     *
     * @param string $id An identifier for this table (optional).
     * @retrn none
     */
    function report($id = '') {
        $this->id            = $id;
        $this->table         = new stdClass;
        $this->columns       = array();
        $this->align         = array();
        $this->sortable      = array();
        $this->wrap          = array();
        $this->defsort       = '';
        $this->defdir        = '';
        $this->data          = array();
        $this->numrecs       = 0;
        $this->baseurl       = '';
        $this->sort          = '';
        $this->dir           = '';
        $this->page          = 0;
        $this->perpage       = 0;
        $this->search        = '';
        $this->alpha         = '';
        $this->fileformats   = array();
        $this->top           = '';
        $this->bottom        = '';
        $this->signatureline = false;
        $this->dateline      = false;
    }


/////////////////////////////////////////////////////////////////////
//                                                                 //
//  DATA FUNCTIONS:                                                //
//                                                                 //
/////////////////////////////////////////////////////////////////////


    /**
     * Set the array of report columns.
     *
     * @param string $id       The column ID.
     * @param string $name     The textual name displayed for the column header.
     * @param string $align    Column alignment ('left', 'center' or 'right').
     * @param bool   $sortable Whether the column is sortable or not.
     * @param bool   $wrap     If set to true the column will not automatically wrap.
     * @return bool True on success, False otherwise.
     */
    function add_column($id, $name, $align = 'left', $sortable = false, $wrap = false) {
        if ($align != 'left' || $align != 'center' || $align != 'right') {
            $align = 'left';
        }

        $this->headers[$id]  = $name;
        $this->align[$id]    = $align;
        $this->sortable[$id] = $sortable;
        $this->wrap[$id]     = $wrap;
    }


    /**
     * Set the title of this report (only really used in a PDF download
     */
    function set_title($title) {
        $this->title = $title;
    }


    /**
     * Set a column to default sorting.
     *
     * @param string $column The column ID.
     * @param string $dir    The sort direction (ASC, DESC).
     */
    function set_default_sort($column, $dir = 'ASC') {
        if (!isset($this->headers[$column]) || !$this->sortable[$column]) {
            return false;
        }

        if ($dir != 'ASC' || $dir != 'DESC') {
            $dir = 'ASC';
        }

        $this->defsort = $column;
        $this->defdir  = $dir;

        return true;
    }


    /**
     * Define the base URL for this report.
     *
     * @param string $url The base URL.
     * @return none
     */
    function set_baseurl($url) {
        $this->baseurl = $url;
    }


    /**
     * Define the paging URL for this report.
     *
     * @param string $url The paging URL.
     * @return none
     */
    function set_pageurl($url) {
        $this->pageurl = $url;
    }


    /**
     * Get the data to display for this table page.
     *
     * @TODO: This function must be extended in a subclass.
     */
    function get_data() {
    /// This function must be extended to load data into the table.
    }


/////////////////////////////////////////////////////////////////////
//                                                                 //
//  DISPLAY FUNCTIONS:                                             //
//                                                                 //
/////////////////////////////////////////////////////////////////////


    /**
     * Display the table with data.
     */
    function display() {
        global $CFG;

        $output = '';

        if (empty($this->data)) {
            return $output;
        }

        foreach ($this->headers as $column => $header) {
            $id = $column;

            if ($this->sortable[$id]) {
                if ($this->sort != $column) {
                    $columnicon = "";
                    $columndir = "ASC";
                } else {
                    $columndir  = $this->dir == "ASC" ? "DESC":"ASC";
                    $columnicon = $this->dir == "ASC" ? "down":"up";
                    $columnicon = " <img src=\"$CFG->pixpath/t/$columnicon.gif\" alt=\"\" />";
                }
                $$column = '<a href="' . $this->baseurl . '&amp;sort=' . $id . '&amp;dir=' .
                           $columndir . '&amp;namesearch=' . urlencode(stripslashes($this->search)) .
                           '&amp;alpha=' . $this->alpha . '">' . $header . '</a>' . $columnicon;
            } else {
                $$column = $header;
            }

            $this->table->head[]  = $$column;
            $this->table->align[] = $this->align[$id];
            $this->table->wrap[]  = $this->wrap[$id];
        }

        foreach ($this->data as $datum) {
            $row = array();

            if (is_array($datum) && (strtolower($datum[0]) == 'hr')) {
                $row = 'hr';
            } else {
                foreach ($this->headers as $id => $header) {
                    if (isset($datum->$id)) {
                        $row[$id] = $datum->$id;
                    } else {
                        $row[$id] = '';
                    }
                }
            }

            $this->table->data[] = $row;
        }

        $this->table->width = '100%';

        $output .= print_table($this->table, true);

        return $output;
    }


    /**
     * Get the data needed for a downloadable version of the report (all data,
     * no paging necessary) and format it accordingly for the download file
     * type.
     *
     * NOTE: It is expected that the valid format types will be overridden in
     * an extended report class as the array is empty by default.
     *
     * @param string $format A valid format type.
     */
    function download($format) {
        global $CFG;

        $output = '';

        if (empty($this->data)) {
            return $output;
        }


        $title = !empty($this->title) ? $this->title : 'Census Report';
        $filename = !empty($this->filename) ? $this->filename : 'censusreport';
        $top = !empty($this->top) ? $this->top : '';

        switch ($format) {
            case 'csv':
                $filename .= '.csv';

                if (isset($_SERVER['HTTP_USER_AGENT']) &&
                    (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false)) {
                    header('Expires: 0');
                    header('Cache-Control: private, pre-check=0, post-check=0, max-age=0, must-revalidate');
                    header('Connection: Keep-Alive');
                    header('Content-Language: en-us');
                    header('Keep-Alive: timeout=5, max=100');
                    header('Pragma: no-cache');
                    header('Pragma: expires');
                    header('Expires: Mon, 20 Aug 1969 09:23:00 GMT');
                    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
                    header('Content-Transfer-Encoding: ascii');
                    header('Content-Disposition: attachment; filename=' . $filename);
                    header('Content-Type: text/comma-separated-values');
                } else {
                    header('Content-Transfer-Encoding: ascii');
                    header('Content-Disposition: attachment; filename=' . $filename);
                    header('Content-Type: text/comma-separated-values');
                }

                echo $title . "\n";
                echo $top . "\n";

                $row = array();

                foreach ($this->headers as $header) {
                    $row[] = $this->csv_escape_string(strip_tags($header));
                }

                echo implode(',', $row) . "\n";

                foreach ($this->data as $datum) {
                    if (!is_object($datum)) {
                        continue;
                    }

                    $row = array();

                    foreach ($this->headers as $id => $header) {
                        if (isset($datum->$id)) {
                            $row[] = $this->csv_escape_string($datum->$id);
                        } else {
                            $row[] = '""';
                        }
                    }

                    echo implode(',', $row) . "\n";
                }

                break;

            case 'excel':
                require_once($CFG->libdir . '/excellib.class.php');

                $filename .= '.xls';

            /// Creating a workbook
                $workbook = new MoodleExcelWorkbook('-');

            /// Sending HTTP headers
                $workbook->send($filename);

            /// Creating the first worksheet
                $sheettitle  = get_string('studentprogress', 'reportstudentprogress');
                $myxls      =& $workbook->add_worksheet($sheettitle);

            /// Format types
                $format =& $workbook->add_format();
                $format->set_bold(0);
                $formatbc =& $workbook->add_format();
                $formatbc->set_bold(1);
                $formatbc->set_align('center');
                $formatb =& $workbook->add_format();
                $formatb->set_bold(1);
                $formaty =& $workbook->add_format();
                $formaty->set_bg_color('yellow');
                $formatc =& $workbook->add_format();
                $formatc->set_align('center');
                $formatr =& $workbook->add_format();
                $formatr->set_bold(1);
                $formatr->set_color('red');
                $formatr->set_align('center');
                $formatg =& $workbook->add_format();
                $formatg->set_bold(1);
                $formatg->set_color('green');
                $formatg->set_align('center');

                $rownum = 0;
                $colnum = 0;

                foreach ($this->headers as $header) {
                    $myxls->write($rownum, $colnum++, $header, $formatbc);
                }

                foreach ($this->data as $datum) {
                    if (!is_object($datum)) {
                        continue;
                    }

                    $rownum++;
                    $colnum = 0;

                    foreach ($this->headers as $id => $header) {
                        if (isset($datum->$id)) {
                            $myxls->write($rownum, $colnum++, $datum->$id, $format);
                        } else {
                            $myxls->write($rownum, $colnum++, '', $format);
                        }
                    }
                }

                $workbook->close();

                break;

            case 'pdf':
                require_once('cr_pdf.php');

                $filename .= '.pdf';

                $newpdf = new CR_PDF('L', 'in', 'letter');
                $newpdf->SetFont('helvetica', '', 9);

                /// Handle page titles:
                if (!isset($this->titlealign)) {
                    $this->titlealign = 'C';
                }
                if (!isset($this->fontsize)) {
                    $this->fontsize = 16;
                }
                //// Handle text at the top of the page
                if(!isset($this->topalign)) {
                    $this->topalign = 'L';
                }

                // Punt values needed to our special pdf class (for headers & footers)
                $newpdf->titlealign    = $this->titlealign;
                $newpdf->fontsize      = $this->fontsize;
                $newpdf->topalign      = $this->topalign;
                $newpdf->headers       = $this->headers;
                $newpdf->top           = $this->top;
                $newpdf->bottom        = $this->bottom;
                $newpdf->signatureline = $this->signatureline;
                $newpdf->dateline      = $this->dateline;

                $top_lines = explode("\n", $this->top);
                $top_size  = sizeof($top_lines);

                $bottom_lines = explode("\n", $this->bottom);
                $bottom_size = sizeof($bottom_lines);

                $newpdf->setAutoPageBreak(true, 1.2 + ($bottom_size * 0.2));


                $twidth  = 0;
                $heights = array();
                $widths  = array();
                $rownum  = 0;

                /// PASS 1 - Calculate sizes.
                foreach ($this->headers as $id => $header) {
                    $widths[$id] = $newpdf->GetStringWidth($header) + 0.2;
                    $twidth     += $widths[$id];
                }

                $row = 0;

                foreach ($this->data as $datum) {
                    if (!isset($heights[$row])) {
                        $heights[$row] = 0;
                    }

                    foreach ($this->headers as $id => $header) {
                        if (isset($datum->$id)) {
                            $width = $newpdf->GetStringWidth($datum->$id) + 0.2;

                            // Cell doesn't do wrapping.
                            $lines = 1;
                            if ($width > $widths[$id]) {
//                                $lines = ceil($width / $widths[$id]);
                                $widths[$id] = $width;
                            }

                            $height = $lines * 0.2;

                            if ($height > $heights[$row]) {
                                $heights[$row] = $height;
                            }
                        }
                    }

                    $row++;
                }

                $newpdf->widths = $widths;
                $newpdf->AddPage();
                $newpdf->SetFont('helvetica', '', 9);
                $newpdf->SetFillColor(225, 225, 225);

                $row = 0;

                foreach ($this->data as $datum) {
                    if (is_array($datum) && (strtolower($datum[0]) == 'hr')) {
                        $curx = $newpdf->GetX();
                        $cury = $newpdf->GetY() + 0.1;
                        $endx = 0;
                        $endy = $cury;

                        foreach ($widths as $width) {
                            $endx += $width;
                        }

                        $newpdf->Line($curx, $cury, $endx, $endy);

                        $newpdf->SetX($curx + 0.1);

                    } else {
                        $align = 'L';
                        foreach ($this->headers as $id => $header) {
                            $text = '';

                            if (isset($datum->$id)) {
                                $text = $datum->$id;
                            }
                            $newpdf->Cell($widths[$id], $heights[$row], $text, 0, 0, $align, 0);
                            $align = 'C';
                        }
                    }

                    $newpdf->Ln();
                    $row++;
                }

                $this->pdf_output($newpdf, $filename, 'I');

                break;

            default:
                return $output;
                break;
        }
    }


    function pdf_output($pdf, $name = '', $dest = '') {
        ob_start();
        //Output PDF to some destination
        //Normalize parameters
        if(is_bool($dest)) {
            $dest=$dest ? 'D' : 'F';
        }
        $dest=strtoupper($dest);
        if($dest=='') {
            if($name=='') {
                $name='doc.pdf';
                $dest='I';
            } else {
                $dest='F';
            }
        }
        $pdf->Output($name,$dest);
        return 0;
    }


    /**
     * Makes a string safe for CSV output.
     *
     * Replaces unsafe characters with whitespace and escapes
     * double-quotes within a column value.
     *
     * @param string $input The input string.
     * @return string A CSV export 'safe' string.
     */
    function csv_escape_string($input) {
        $input = ereg_replace("[\r\n\t]", ' ', $input);
        $input = ereg_replace('"', '""', $input);
        $input = '"' . $input . '"';

        return $input;
    }


    /**
     * Print the download menu.
     *
     * @param none
     * @return string HTML output for display.
     */
    function print_download_menu() {
        $output = '';

        if (!empty($this->fileformats)) {
            $output .= '<form action="reportdownload.php" method="post">' . "\n";

        /// Print out the necessary hidden form vars.
            $parts = explode('?', $this->baseurl);
            if (count($parts) == 2 && strlen($parts[1])) {
                $args = explode('&amp;', $parts[1]);

                if (count($args) === 0) {
                    $args = exploe('&amp;', $parts[1]);
                }

                if (!empty($args)) {
                    foreach ($args as $arg) {
                        $vals = explode('=', $arg);

                        if (!empty($vals[1])) {
                            $output .= '<input type="hidden" name="' . $vals[0] .
                                       '" value="' . urldecode($vals[1]) . '" />';
                        }
                    }
                }
            }

            $output .= cm_choose_from_menu($this->fileformats, 'download', '', 'choose', '', '0', true);
            $output .= '<input type="submit" value="Download report" />' . "\n";
            $output .= '</form>' . "\n";
        }

        return $output;
    }


    /**
     * Print the initial, paging, and search headers for the table.
     *
     * @param none
     * @return string HTML output for display.
     */
    function print_header() {
        $output = '';

        $alphabet = explode(',', get_string('alphabet'));
        $strall   = get_string('all');

    /// Bar of first initials
        $output .= "<p style=\"text-align:center\">";
        $output .= 'Name'." : ";
        if ($this->alpha) {
            $output .= " <a href=\"{$this->baseurl}&amp;sort=name&amp;dir=ASC&amp;perpage=" .
                       "{$this->perpage}\">$strall</a> ";
        } else {
            $output .= " <b>$strall</b> ";
        }
        foreach ($alphabet as $letter) {
            if ($letter == $this->alpha) {
                $output .= " <b>$letter</b> ";
            } else {
                $output .= " <a href=\"{$this->baseurl}&amp;sort=name&amp;dir=ASC&amp;perpage=" .
                           "{$this->perpage}&amp;alpha=$letter\">$letter</a> ";
            }
        }
        $output .= "</p>";

        $output .= print_paging_bar($this->numrecs, $this->page, $this->perpage,
                                    "{$this->baseurl}&amp;sort={$this->sort}&amp;dir={$this->dir}&amp;" .
                                    "perpage={$this->perpage}&amp;alpha={$this->alpha}&amp;search=" .
                                    urlencode(stripslashes($this->search)) . "&amp;", 'page', false, true);

        $output .= '<table class="searchbox" style="margin-left:auto;margin-right:auto" cellpadding="10"><tr><td>';
        $output .= '<form action="index.php" method="get"><fieldset class="invisiblefieldset">';

    /// Print out the necessary hidden form vars.
        $parts = explode('?', $this->baseurl);
        if (count($parts) == 2 && strlen($parts[1])) {
            $args = explode('&amp;', $parts[1]);

            if (count($args) === 0) {
                $args = exploe('&amp;', $parts[1]);
            }

            if (!empty($args)) {
                foreach ($args as $arg) {
                    $vals = explode('=', $arg);

                    if (!empty($vals[1]) && $vals[1] != 'search') {
                        $output .= '<input type="hidden" name="' . $vals[0] .
                                   '" value="' . $vals[1] . '" />';
                    }
                }
            }
        }

        $output .= '<input type="text" name="search" value="' . s($this->search, true) . '" size="20" />';
        $output .= '<input type="submit" value="Search" />';
        if (!empty($this->search)) {
            $output .= '<input type="button" onclick="document.location=\'' . $this->baseurl .
                 '&amp;sort=' . $this->sort . '&amp;dir=' . $this->dir . '&amp;perpage=' .
                 $this->perpage . '\'"value="Show all users" />';
        }
        $output .= '</fieldset></form>';
        $output .= '</td></tr></table>';

        return $output;
    }


    /**
     * Print the paging footer for the table.
     *
     * @param none
     * @return string HTML output for display.
     */
    function print_footer() {
        $output = '';

        $output .= print_paging_bar($this->numrecs, $this->page, $this->perpage,
                                    "{$this->baseurl}&amp;sort={$this->sort}&amp;dir={$this->dir}&amp;" .
                                    "perpage={$this->perpage}&amp;alpha={$this->alpha}&amp;search=" .
                                    urlencode(stripslashes($this->search)) . "&amp;", 'page', false, true);

        return $output;
    }


    /**
     * Main display function.
     *
     * @TODO: This function must be extended in a subclass (using the same interface parameters).
     */
    function main($sort = '', $dir = '', $page = 0, $perpage = 0, $search = '',
                  $alpha = '', $download = '') {
    /// To be extended.
    }
}
