<?php
//HTML2PDF by ClÈment Lavoillotte
//ac.lavoillotte@noos.fr
//webmaster@streetpc.tk
//http://www.streetpc.tk
// Rev:
//    Remote Learner Custom Edit By John T. Macklin 8/13/2008 11:33:58 PM
//    +180 Added Customized function function Output($name='',$dest='') to
//    include the correct customized headers for MSIE Browser Type when ($dest='I')

require_once($CFG->dirroot.'/mod/mgm/oppdflib.class.php');

class LETTERPDF extends OPPDF
{
//variables of html parser
var $B;
var $I;
var $U;
var $HREF;
var $fontList;
var $issetfont;
var $issetcolor;
var $username;
var $cabecera1;
var $cabecera2;
var $foot1;
var $foot2;


function LETTERPDF($data,$orientation='P',$unit='mm',$format='A4'){
    //Call parent constructor
    $this->OPPDF($orientation,$unit,$format);
    //Initialization
    $this->B=0;
    $this->I=0;
    $this->U=0;
    $this->HREF='';
    $this->SetLeftMargin(20);
    $this->fontlist=array("arial","times","courier","helvetica","symbol");
    $this->issetfont=false;
    $this->isetcolor=false;
    $this->username=false;
    $this->titleOp=false;
    $this->cabecera1=false;
    $this->cabecera2=false;
}

public function Header()
  {
    global $CFG;
    $this->Image($CFG->dirroot. '/mod/mgm/pix/letter_001.jpg',6,6,25.9, 28.3);
    $this->SetFont('Arial','B',10);
    $this->setXY(35,15);
    $this->MultiCell(100,4,$this->getTitleOp(),0,'L');
    $this->SetFont('Arial','',7);
    $this->setXY(120,8);
    $this->SetFillColor(200);
    $this->MultiCell(65,3,$this->getCabecera1(),0,'L', true);
    $this->setXY(50,32);
    $this->MultiCell(150,3,$this->getCabecera2(),0,'R');
    $this->SetLineWidth(.3);
    $this->Line(6,36,200,36);
    $this->Ln(5);
  }
  //pie de pagina
public function Footer()
  {
  	$this->SetLineWidth(.3);
    $this->Line(6,274,200,274);
    $this->SetXY(20,-20);
    $this->SetFont('Arial','I',8);
    $this->MultiCell(80,3,iconv('UTF-8', 'windows-1252', $this->getFooter1()), 0, 'L');
    $this->SetXY(150,-20);
    $this->MultiCell(80,3,iconv('UTF-8', 'windows-1252', $this->getFooter2()), 0, 'L');
  }

public function opFooter($foot1, $foot2)
  {
    $this->foot1=$foot1;
    $this->foot2=$foot2;
  }

public function getUsername(){
    return $this->username;
}

public function getFooter1() {
    return $this->foot1;
}
public function getFooter2() {
    return $this->foot2;
}

public function setFooter1($str)
  {
    $this->foot1=$str;
  }

public function setFooter2($str)
  {
    $this->foot2=$str;
  }

public function setCaberera1($str)
  {
    $this->caberera1=$str;
  }

public function getCabecera1() {
    return $this->caberera1;
}

public function setCaberera2($str)
  {
    $this->caberera2=$str;
  }

public function getCabecera2() {
    return $this->caberera2;
}

public function setTitleOp($str)
  {
    $this->titleOp=$str;
  }

public function getTitleOp() {
	 return $this->titleOp;
}

public function opCabecera($title, $head1, $head2)
  {
    $this->setCaberera1(iconv('UTF-8', 'windows-1252', $head1));
    $this->setCaberera2(iconv('UTF-8', 'windows-1252', $head2));
    $this->setTitleOp(iconv('UTF-8', 'windows-1252', $title));
  }

public function AddLetter($letterhead, $letterbody, $letterfoot){
	 global $CFG;
    $this->AddPage();
    $this->SetFont('Arial','B',10);
    $this->setXY(6,40);
    $this->MultiCell(194,4,iconv('UTF-8', 'windows-1252', $letterhead),0, 'R');
    $this->SetFont('Arial','',10);
    $this->setXY(20,70);
    $this->MultiCell(170,4,iconv('UTF-8', 'windows-1252', $letterbody),0, 'J');
    $y=$this->GetY();
    $this->Image($CFG->dirroot. '/mod/mgm/pix/letter_002.jpg',50,$y,43.2, 20.4);
    $this->Image($CFG->dirroot. '/mod/mgm/pix/letter_003.png',95,$y,32.9, 34.9);
    $this->setXY(6,$y+40);
    $this->MultiCell(200,4,iconv('UTF-8', 'windows-1252', $letterfoot), 0, 'C');
}

}
