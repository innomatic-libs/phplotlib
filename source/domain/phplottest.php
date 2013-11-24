<?php

require_once('innomatic/wui/Wui.php');
require_once('innomatic/wui/widgets/WuiWidget.php');
require_once('innomatic/wui/widgets/WuiContainerWidget.php');
require_once('innomatic/wui/dispatch/WuiEventsCall.php');
require_once('innomatic/wui/dispatch/WuiEvent.php');
require_once('innomatic/wui/dispatch/WuiEventRawData.php');
require_once('innomatic/wui/dispatch/WuiDispatcher.php');

    global $gXml_def;

$gWui = Wui::instance('wui');
$gWui->LoadWidget( 'innomaticpage' );
$gWui->LoadWidget( 'xml' );

$gXml_def = '';

$gMain_disp = new WuiDispatcher( 'view' );

$gMain_disp->AddEvent( 'default', 'main_default' );
function main_default( $eventData )
{
    global $gXml_def;

$example_data = array(

	array("80",0.0,20,4,5,6),
	array("81",2.0,30,5,6,7),
	array("82",3.0,40,5,7,8),
	array("83",4.0,50,3,6,3),
	array("84",4.4,40,3,6,5),
	array("85",5.4,40,5,6,5),
	array("86",5.5,40,7,6,5),
	array("87",7,35,0.0,0.0,""),
	array("88",7.4,40,14,16,25),
	array("89",7.6,40,6,6,5),
	array("90",8.2,40,3,6,5),
	array("91",8.5,40,8,6,9),
	array("92",9.3,40,5,6,5),
	array("93",9.6,40,9,6,7),
	array("94",9.9,40,2,6,5),
	array("95",10.0,40,3,6,8),
	array("96",10.4,40,3,6,5),
	array("97",10.5,40,3,6,5),
	array("98",10.8,40,3,6,5),
	array("99",11.4,40,3,6,5),
	array("00",12.0,40,3,7,5),
	array("01",13.4,40,3,5,3),
	array("02",14.0,30,3,5,6)
);

$legend = array(
    'Prodotto A',
    'Prodotto B',
    'Prodotto C',
    'Prodotto D',
    'Prodotto E'
    );

$gXml_def =
'<vertgroup>
  <children>

    <label>
      <args>
        <label>Grafico</label>
      </args>
    </label>

    <vertframe>
      <children>

        <phplot>
          <args>
            <data type="array">'.WuiXml::encode( $example_data ).'</data>
            <width>600</width>
            <height>350</height>
            <legend type="array">'.WuiXml::encode( $legend ).'</legend>
            <plottype>'.$eventData['plottype'].'</plottype>
            <title>Elaborazione fatturato</title>
          </args>
        </phplot>

      </children>
    </vertframe>

    <horizbar/>

    <horizgroup>
      <children>

        <button>
          <args>
            <themeimage>button_ok</themeimage>
            <horiz>true</horiz>
            <themeimagetype>mini</themeimagetype>
            <label>Bars</label>
            <action type="encoded">'.urlencode(
                WuiEventsCall::buildEventsCallString(
                    '',
                    array(
                        array(
                            'view',
                            'default',
                            array(
                                'plottype' => 'bars'
                                )
                            )
                        )
                    )
                ).'</action>
          </args>
        </button>

        <button>
          <args>
            <themeimage>button_ok</themeimage>
            <horiz>true</horiz>
            <themeimagetype>mini</themeimagetype>
            <label>Lines</label>
            <action type="encoded">'.urlencode(
                WuiEventsCall::buildEventsCallString(
                    '',
                    array(
                        array(
                            'view',
                            'default',
                            array(
                                'plottype' => 'lines'
                                )
                            )
                        )
                    )
                ).'</action>
          </args>
        </button>

        <button>
          <args>
            <themeimage>button_ok</themeimage>
            <horiz>true</horiz>
            <themeimagetype>mini</themeimagetype>
            <label>Linepoints</label>
            <action type="encoded">'.urlencode(
                WuiEventsCall::buildEventsCallString(
                    '',
                    array(
                        array(
                            'view',
                            'default',
                            array(
                                'plottype' => 'linepoints'
                                )
                            )
                        )
                    )
                ).'</action>
          </args>
        </button>

        <button>
          <args>
            <themeimage>button_ok</themeimage>
            <horiz>true</horiz>
            <themeimagetype>mini</themeimagetype>
            <label>Area</label>
            <action type="encoded">'.urlencode(
                WuiEventsCall::buildEventsCallString(
                    '',
                    array(
                        array(
                            'view',
                            'default',
                            array(
                                'plottype' => 'area'
                                )
                            )
                        )
                    )
                ).'</action>
          </args>
        </button>

        <button>
          <args>
            <themeimage>button_ok</themeimage>
            <horiz>true</horiz>
            <themeimagetype>mini</themeimagetype>
            <label>Points</label>
            <action type="encoded">'.urlencode(
                WuiEventsCall::buildEventsCallString(
                    '',
                    array(
                        array(
                            'view',
                            'default',
                            array(
                                'plottype' => 'points'
                                )
                            )
                        )
                    )
                ).'</action>
          </args>
        </button>

        <button>
          <args>
            <themeimage>button_ok</themeimage>
            <horiz>true</horiz>
            <themeimagetype>mini</themeimagetype>
            <label>Pie</label>
            <action type="encoded">'.urlencode(
                WuiEventsCall::buildEventsCallString(
                    '',
                    array(
                        array(
                            'view',
                            'default',
                            array(
                                'plottype' => 'pie'
                                )
                            )
                        )
                    )
                ).'</action>
          </args>
        </button>

      </children>
    </horizgroup>

  </children>
</vertgroup>';
}

$gMain_disp->Dispatch();

$gWui->AddChild( new WuiInnomaticPage( 'page', array(
    'pagetitle' => 'phplot test',
    'maincontent' => new WuiXml(
        'page', array(
            'definition' => $gXml_def
            ) ),
    'icon' => 'txt'
    ) ) );

$gWui->Render();

// --------------

InnomaticContainer::instance('innomaticcontainer')->halt();

require_once('phplot/PHPlot.php');

//Define the Object
$graph = new PHPlot( 500, 300 );

//Define some data
//include("./regression_data.php");

$example_data = array(

	array("A",0.0,20,4,5,6),
	array("B",2.0,30,5,6,7),
	array("C",3.0,40,5,7,8),
	array("D",4.0,50,3,6,3),
	array("E",4.4,40,3,6,5),
	array("F",5.4,40,5,6,5),
	array("G",5.5,40,7,6,5),
	array("H",7,35,0.0,0.0,""),
	array("I",7.4,40,14,16,25),
	array("J",7.6,40,6,6,5),
	array("K",8.2,40,3,6,5),
	array("L",8.5,40,8,6,9),
	array("M",9.3,40,5,6,5),
	array("N",9.6,40,9,6,7),
	array("O",9.9,40,2,6,5),
	array("P",10.0,40,3,6,8),
	array("Q",10.4,40,3,6,5),
	array("R",10.5,40,3,6,5),
	array("S",10.8,40,3,6,5),
	array("T",11.4,40,3,6,5),
	array("U",12.0,40,3,7,5),
	array("V",13.4,40,3,5,3),
	array("W",14.0,30,3,5,6)
);

//$graph->SetXGridLabelType("time");

//Set the data type
//$graph->SetDataType("linear-linear");

    //$graph->SetPointShape("halfline");

//Remove the X data labels
//$graph->SetXGridLabelType("none");

//Load the data into data array
$graph->SetDataValues($example_data);
$graph->SetDataColors(array("blue",'white'),array("black"));
    $graph->SetLineWidth("1");
    $graph->$line_style = array('dashed','dashed','solid','dashed','dashed','solid');

//s$graph->SetLineStyles( 'dashed' );
//    $graph->SetTickLength(2);
//Draw the graph
$graph->DrawGraph();

/*
echo '<pre>';
print_r( $example_data );
echo '</pre>';
*/

?>