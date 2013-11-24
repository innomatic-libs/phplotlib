<?php

require_once('innomatic/webapp/WebAppHandler.php');
require_once('innomatic/webapp/WebAppProcessor.php');

class PHPlotWebAppHandler extends WebAppHandler {
	public function init() {
	}

	public function doGet(WebAppRequest $req, WebAppResponse $res) {
		// Bootstraps Innomatic
		require_once('innomatic/core/InnomaticContainer.php');
		$innomatic = InnomaticContainer::instance('innomaticcontainer');

		// Sets Innomatic base URL
		$baseUrl = '';
		$webAppPath = $req->getUrlPath();
		if (!is_null($webAppPath) && $webAppPath != '/') {
			$baseUrl = $req->generateControllerPath($webAppPath, true);
		}
		$innomatic->setBaseUrl($baseUrl);

		$innomatic->setInterface(InnomaticContainer::INTERFACE_WEB);
		$home = WebAppContainer::instance('webappcontainer')->getCurrentWebApp()->getHome();
		$innomatic->bootstrap($home, $home.'core/conf/innomatic.ini');
		
		$id = basename($req->getParameter('id'));
		//$id = basename($_GET['id']);
		$args = unserialize(file_get_contents(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/phplot/'.$id));

		require_once('phplot/PHPlot.php');
		$graph = new PHPlot( $args['width'], $args['height'] );
		$graph->SetIsInline( '1' );

		//$graph->SetDataColors( array("blue",'white'),array("black") );
		//$graph->$line_style = array('dashed','dashed','solid','dashed','dashed','solid');

		// Base

		$graph->SetDataValues( $args['data'] );
		$graph->SetPlotType( $args['plottype'] );

		// Appearance

		$graph->SetPointShape( $args['pointshape'] );
		$graph->SetPointSize( $args['pointsize'] );
		$graph->SetTitle( $args['title'] );

		// Color

		$graph->SetBackgroundColor( $args['backgroundcolor'] );
		$graph->SetGridColor( $args['gridcolor'] );
		if ( count( $args['legend'] ) ) $graph->SetLegend( $args['legend'] );
		$graph->SetLineWidth( $args['linewidth'] );
		$graph->SetTextColor( $args['textcolor'] );

		$graph->SetDataColors( array( array(145,165,207), array(114,167,112), array(71,85,159), array(175,83,50), array(247,148,53), array(240,231,125), array(154,204,203), array(201,164,196) ), 'black' );

		//$graph->data_color = array( array(145,165,207), array(114,167,112), array(71,85,159), array(175,83,50), array(247,148,53), array(240,231,125), array(154,204,203), array(201,164,196) );
		//array('blue','green','yellow','red','orange');

		$graph->DrawGraph();
		unlink(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/phplot/'.$id);
	}

	public function doPost(WebAppRequest $req, WebAppResponse $res) {
		$this->doGet($req, $res);
	}

	public function destroy() {
	}

	protected function getRelativePath(WebAppRequest $request) {
		$result = $request->getPathInfo();
		require_once('innomatic/io/filesystem/DirectoryUtils.php');
		return DirectoryUtils::normalize(strlen($result) ? $result : '/');
	}

	/**
	 * Prefix the context path, our webapp emulator and append the request
	 * parameters to the redirection string before calling sendRedirect.
	 *
	 * @param $request WebAppRequest
	 * @param $redirectPath string
	 * @return string
	 * @access protected
	 */
	protected function getURL(WebAppRequest $request, $redirectPath) {
		$result = '';
		$webAppPath = $request->getUrlPath();
		if (!is_null($webAppPath) && $webAppPath != '/') {
			$result = $request->generateControllerPath($webAppPath, true);
		}

		$result .= $redirectPath;

		$query = $request->getQueryString();
		if (!is_null($query)) {
			$result .= '?'.$query;
		}

		return $result;
	}
}

?>