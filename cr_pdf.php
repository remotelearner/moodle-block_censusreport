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
 * Ten percent pdf class.
 *
 * @package   blocks-censusreport
 * @author    Tyler Bannister <tyler.bannister@remote-learner.net>
 * @copyright 2011 Remote Learner - http://www.remote-learner.net/
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->libdir . '/tcpdf/tcpdf.php');

/**
 * Census report pdf class, extends the fast pdf class.
 *
 * @author    Tyler Bannister <tyler.banniser@remote-learner.net>
 * @copyright 2011 Remote Learner - http://www.remote-learner.net/
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class CR_PDF extends TCPDF {

    public $title         = "";
    public $signatureline = false;
    public $dateline      = false;
    public $headers       = array();
    public $fontsize      = 16;
    public $titlealign    = "C";
    public $topalign      = "L";
    public $top           = "";
    public $bottom        = "";
    public $widths        = array();

    /**
     * Override default header (which is blank)
     *
     * @uses $CFG
     */
    function Header() {
        global $CFG;

        $this->setY(1);
        $this->SetFont('helvetica', '',    $this->fontsize);
        $this->MultiCell(0, 0.2,       $this->title, 0, $this->titlealign);
        $this->SetFont('helvetica', '',    $this->fontsize - 2);
        $this->MultiCell(0, 0.2, "\n". $this->top, 0, $this->topalign);
        $this->Ln(0.2);
        $this->SetFont('helvetica', '', 9);
        $this->SetFillColor(225, 225, 225);

        if (file_exists($CFG->dirroot.'/blocks/censusreport/pix/header_logo.jpg')) {
            $this->Image($CFG->dirroot.'/blocks/censusreport/pix/header_logo.jpg', 0, 0, 9.486, 1.278, 'JPG');
        }

        foreach ($this->headers as $id => $header) {
            $text = str_replace(' ', "\n", $header);
            $this->Cell($this->widths[$id], 0.2, $text, 1, 0, 'C', 1);
        }
        $this->Ln();
        $this->tMargin = $this->getY();
    }

    /**
     * Override the default footer (which is blank)
     *
     * @uses $CFG
     */
    function Footer() {
        global $CFG;

        $this->SetY(-1.5);

        if ($this->signatureline) {
            $this->SetFont('helvetica', '', $this->fontsize - 2);
            $this->MultiCell(0, 0.2, get_string('certified', 'block_censusreport') .":\n",
                               0, $this->topalign);
            $this->MultiCell(0, 0.2, get_string('signature', 'block_censusreport') .':',
                               0, $this->topalign);
            $x = $this->getX();
            $y = $this->getY();
            $this->line($x+1, $y-0.02, $x+3, $y-0.02);
        }

        if ($this->dateline) {
            $this->SetFont('helvetica', '', $this->fontsize - 2);
            $this->MultiCell(0, 0.2, get_string('date') .':', 0, $this->topalign);
            $x = $this->getX();
            $y = $this->getY();
            $this->line($x+1, $y-0.02, $x+3, $y-0.02);
        }

        if (file_exists($CFG->dirroot .'/blocks/censusreport/pix/moodlelogo.jpg')) {
            $this->Image($CFG->dirroot .'/blocks/censusreport/pix/moodlelogo.jpg', 4.0, 8.0);
        }
        $this->SetFont('helvetica', '', $this->fontsize - 2);
        $this->MultiCell(0, 0.2, $this->bottom, 0, $this->topalign);
        $this->Text(5.5, 8.25, $CFG->wwwroot);

    }

}