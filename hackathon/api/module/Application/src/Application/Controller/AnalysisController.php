<?php

/**
 * This file is part of Agora_analitics.
 *
 * (c) Bruno Santos Ferreira <brunosfweb@gmail.com>
 *     
 */

namespace Application\Controller;

use Zend\View\Model\ViewModel;
use Application\Entity\Researcher;
use Application\Controller\Plugin\ScriptExec;
use Zend\View\Model\JsonModel;
use Application\Entity\Analysis;
use Zend\Http\Client;
use Zend\Json\Json;
use ZipArchive;
use Application\Entity\Log;
use Application\Job\DownloadCvLattesCnpq;
use Zend\Db\Sql\Select;
use Application\Entity\Job;

class AnalysisController extends ApplicationController
{
	protected $queue;
	protected $jobManager;

	public function listAction()
	{

		$view = new ViewModel();
		$permanents = array();
		$temps      = array();
		$analyses = $this->getModel()->getList('Application\Entity\Analysis', array(), array('updated' => 'DESC'));

		foreach ($analyses as $analysis) {
			$now = new \Datetime('now');

			$analysis->toUpdate = false;

			if ($analysis->getState() == Analysis::PERMANENT) {

				$update = ((int) date_diff($analysis->getUpdated(), $now)->format('%R%a')) - 61;

				if ($update >= 0 && $analysis->getStatus() != Analysis::RUNNING) {

					$path = getcwd() . '/data/analyses/';
					$analysisPath = $path . $analysis->getId();
					$list     = file_get_contents($analysisPath . '/' . $analysis->getId() . '.list.json');
					$jsonList = json_decode($list, true);

					if (count($jsonList['fiocruz']) >= 150) {
						$analysis->toUpdate = true;
					} else {
						$this->restart($analysis);
					}
				}

				array_push($permanents, $analysis);
			} else {

				$diff = ((int) date_diff($now, $analysis->getCreated())->format('%R%a')) + 7;

				if ($diff <= 0) {
					$this->getModel()->delete('Application\Entity\Analysis', $analysis->getId());
				} else {
					array_push($temps, $analysis);
				}
			}
		}
		$settings = $this->getModel()->get('Application\Entity\Settings', 1);
		$this->layout()->setVariable('settings', $settings);

		$view->setVariables(
			array(
				'permanents' => $permanents,
				'temps' => $temps,
			)
		);

		return $view;
	}

	public function dashboardAction()
	{
		$view = new ViewModel();
		$id = $this->getEvent()->getRouteMatch()->getParam('id', null);
		$analysis = $this->getModel()->get('Application\Entity\Analysis', $id);
		$settings = $this->getModel()->get('Application\Entity\Settings', 1);

		$this->layout()->setVariable('settings', $settings);

		$path = getcwd() . '/data/analyses/';
		$analysisPath = $path . $id;


		$profile    = file_exists($analysisPath . '/perfis.json') ?
			file_get_contents($analysisPath . '/perfis.json') :
			file_get_contents($analysisPath . '/profile.json');

		$orient    = file_exists($analysisPath . '/relatorioOrientacao.json') ?
			file_get_contents($analysisPath . '/relatorioOrientacao.json') :
			file_get_contents($analysisPath . '/advise.json');

		$file    = file_exists($analysisPath . '/relatorioProducaoBibiografica.json') ?
			file_get_contents($analysisPath . '/relatorioProducaoBibiografica.json') :
			file_get_contents($analysisPath . '/publication.json');

		$list    = file_get_contents($analysisPath . '/' . $id . '.list.json');

		$jsonProducoes   = json_decode($file, true);
		$jsonOrientacoes = json_decode($orient, true);
		$jsonPerfil      = json_decode($profile, true);
		$jsonList        = json_decode($list, true);
		//print_r($jsonProducoes);exit;

		$chartPublications = array();

		$chartPublications['Bibliografica'] = [];
		$totalQualis = 0;
		$totalArticles = 0;
		$donutPublications = array();
		$totalDonutQualis = 0;
		$chartUninformedYear = 0;

		if (isset($jsonProducoes['PERIODICO'])) {

			foreach ($jsonProducoes['PERIODICO'] as $ano => $dados) {

				if (!isset($chartPublications['Bibliografica'][$ano])) {
					$chartPublications['Bibliografica'][$ano] = 0;
				}

				$totalArticles += count($dados);
				$chartPublications['Bibliografica'][$ano] += count($dados);


				foreach ($dados as $d) {
					if ($settings->getQualis()) {
						if (isset($d['issn'])) {
							$issn = preg_replace("/(\d{4})(\d{4})/", "\$1-\$2", $d['issn']);
							$qualisInfo = $this->getModel()->getBy(array('issn' => $issn), 'Application\Entity\QualisIssnCapes');
							$qualisInfo = is_array($qualisInfo) ? reset($qualisInfo) : $qualisInfo;

							if ($qualisInfo) {
								$totalQualis += $qualisInfo->getQualisInfo()->getPoints();

								if (!isset($donutPublications[$qualisInfo->getClassification()]))
									$donutPublications[$qualisInfo->getClassification()] = 0;

								++$donutPublications[$qualisInfo->getClassification()];
							} else {
								if (!isset($donutPublications['N/P']))
									$donutPublications['N/P'] = 0;

								++$donutPublications['N/P'];
							}
							$totalDonutQualis++;
						} else {
							if (!isset($donutPublications['N/I']))
								$donutPublications['N/I'] = 0;

							++$donutPublications['N/I'];
						}
					}
				}
			}
		}

		$totalEvents = 0;

		if (isset($jsonProducoes['EVENTO'])) {

			foreach ($jsonProducoes['EVENTO'] as $ano => $dados) {

				if (!isset($chartPublications['Bibliografica'][$ano])) {
					$chartPublications['Bibliografica'][$ano] = 0;
				}
				$totalEvents += count($dados);
				$chartPublications['Bibliografica'][$ano] += count($dados);
			}
		}

		$totalChapters = 0;

		if (isset($jsonProducoes['CAPITULO_DE_LIVRO']) || isset($jsonProducoes['LIVRO'])) {

			foreach ($jsonProducoes['CAPITULO_DE_LIVRO'] as $ano => $dados) {

				if (!isset($chartPublications['Bibliografica'][$ano])) {
					$chartPublications['Bibliografica'][$ano] = 0;
				}
				$totalChapters += count($dados);
				$chartPublications['Bibliografica'][$ano] += count($dados);
			}
		}

		$totalBooks = 0;

		if (isset($jsonProducoes['LIVRO'])) {

			foreach ($jsonProducoes['LIVRO'] as $ano => $dados) {

				if (!isset($chartPublications['Bibliografica'][$ano])) {
					$chartPublications['Bibliografica'][$ano] = 0;
				}
				$totalBooks += count($dados);
				$chartPublications['Bibliografica'][$ano] += count($dados);
			}
		}

		$totalOthers = 0;

		if (isset($jsonProducoes['DEMAIS_TIPOS_DE_PRODUCAO_BIBLIOGRAFICA'])) {

			foreach ($jsonProducoes['DEMAIS_TIPOS_DE_PRODUCAO_BIBLIOGRAFICA'] as $ano => $dados) {

				if (!isset($chartPublications['Bibliografica'][$ano])) {
					$chartPublications['Bibliografica'][$ano] = 0;
				}
				$totalOthers += count($dados);
				$chartPublications['Bibliografica'][$ano] += count($dados);
			}
		}

		$totalJournals = 0;
		if (isset($jsonProducoes['TEXTO_EM_JORNAIS'])) {

			foreach ($jsonProducoes['TEXTO_EM_JORNAIS'] as $ano => $dados) {
				if (!isset($chartPublications['Bibliografica'][$ano]))
					$chartPublications['Bibliografica'][$ano] = 0;

				$chartPublications['Bibliografica'][$ano] += count($dados);
				$totalJournals  += count($dados);

				foreach ($dados as $d) {

					if ($settings->getQualis()) {

						if (isset($d['issn'])) {
							$issn = preg_replace("/(\d{4})(\d{4})/", "\$1-\$2", $d['issn']);
							$qualisInfo = $this->getModel()->getBy(array('issn' => $issn), 'Application\Entity\QualisIssnCapes');
							$qualisInfo = is_array($qualisInfo) ? reset($qualisInfo) : $qualisInfo;

							if ($qualisInfo) {
								$totalQualis += $qualisInfo->getQualisInfo()->getPoints();

								if (!isset($donutPublications[$qualisInfo->getClassification()]))
									$donutPublications[$qualisInfo->getClassification()] = 0;

								++$donutPublications[$qualisInfo->getClassification()];
							} else {
								if (!isset($donutPublications['N/P']))
									$donutPublications['N/P'] = 0;

								++$donutPublications['N/P'];
							}
							$totalDonutQualis++;
						} else {
							if (!isset($donutPublications['N/I']))
								$donutPublications['N/I'] = 0;

							++$donutPublications['N/I'];
						}
					}
				}
			}
		}

		$totalAccepteds = 0;

		if (isset($jsonProducoes['ARTIGO_ACEITO'])) {

			foreach ($jsonProducoes['ARTIGO_ACEITO'] as $ano => $dados) {

				if (!isset($chartPublications['Bibliografica'][$ano])) {
					$chartPublications['Bibliografica'][$ano] = 0;
				}

				$chartPublications['Bibliografica'][$ano] += count($dados);
				$totalAccepteds += count($dados);

				foreach ($dados as $d) {
					if ($settings->getQualis()) {

						if (isset($d['issn'])) {
							$issn = preg_replace("/(\d{4})(\d{4})/", "\$1-\$2", $d['issn']);
							$qualisInfo = $this->getModel()->getBy(array('issn' => $issn), 'Application\Entity\QualisIssnCapes');
							$qualisInfo = is_array($qualisInfo) ? reset($qualisInfo) : $qualisInfo;

							if ($qualisInfo) {
								$totalQualis += $qualisInfo->getQualisInfo()->getPoints();

								if (!isset($donutPublications[$qualisInfo->getClassification()]))
									$donutPublications[$qualisInfo->getClassification()] = 0;

								++$donutPublications[$qualisInfo->getClassification()];
							} else {
								if (!isset($donutPublications['N/P']))
									$donutPublications['N/P'] = 0;

								++$donutPublications['N/P'];
							}
							$totalDonutQualis++;
						} else {
							if (!isset($donutPublications['N/I']))
								$donutPublications['N/I'] = 0;

							++$donutPublications['N/I'];
						}
					}
				}
			}
		}

		$totalOrientations = 0;
		$totalOrientationsInProgress = 0;
		$chartOrientation = array();
		$chartOrientationInProgress = array();

		//var_dump($jsonOrientacoes);exit;

		foreach ($jsonOrientacoes as $type => $dataType) {

			if (
				strpos($type, '_CONCLUIDA_') !== false
				|| strpos($type, '_CONCLUIDAS') !== false
				|| strpos($type, '_CONCLUSAO_') !== false
			) {
				foreach ($dataType as $year => $d) {
					if (!isset($chartOrientation[$year]))
						$chartOrientation[$year] = 0;

					$chartOrientation[$year] += count($d);
					$totalOrientations += count($d);
				}
				continue;
			}

			if (
				strpos($type, '_EM_ANDAMENTO_') !== false
				|| strpos($type, 'INICIACAO_CIENTIFICA') !== false
				|| strpos($type, 'ORIENTACAO-DE-OUTRA-NATUREZA') !== false
			) {
				foreach ($dataType as $year => $d2) {

					if (!isset($chartOrientationInProgress[$year]))
						$chartOrientationInProgress[$year] = 0;

					$chartOrientationInProgress[$year] += count($d2);
					$totalOrientationsInProgress += count($d2);
				}
				continue;
			}
		}

		$list = $jsonList ? reset($jsonList) : null;
		$view->setVariables(
			array(
				'analysis'       => $analysis,
				't_articles'     => $totalArticles,
				't_events'       => $totalEvents,
				't_chapters'     => $totalChapters,
				't_journals'     => $totalJournals,
				't_accepteds'     => $totalAccepteds,
				't_others'       => $totalOthers,
				't_books'     	 => $totalBooks,
				't_orientations' => $totalOrientations,
				't_orientationsInProgress' => $totalOrientationsInProgress,
				't_profiles'     => count($jsonPerfil),
				'chartOrientation' => $chartOrientation,
				'chartOrientationInProgress' => $chartOrientationInProgress,
				'chartPublication' => $chartPublications,
				'donutPublication' => $donutPublications,
				't_donut_publication' => $totalDonutQualis,
				'list'  => $list,
				't_profile' => count($jsonPerfil),
				'is_graph' => file_exists($analysisPath . '/graph.json'),
				'path' => $analysisPath,
				'totalQualis' => $settings->getQualis() ? $totalQualis : null,
				'settings' => $settings
			)
		);
		return $view;
	}

	public function searchAction()
	{
		$view = new ViewModel();
		$id = $this->getEvent()->getRouteMatch()->getParam('id', null);
		$q  = $this->getRequest()->isPost() ? $this->getRequest()->getPost("q") : null;

		$analysis = $this->getModel()->get('Application\Entity\Analysis', $id);

		$path = getcwd() . '/data/analyses/';
		$analysisPath = $path . $id;


		$profile    = file_exists($analysisPath . '/perfis.json') ?
			file_get_contents($analysisPath . '/perfis.json') :
			file_get_contents($analysisPath . '/profile.json');

		$orient    = file_exists($analysisPath . '/relatorioOrientacao.json') ?
			file_get_contents($analysisPath . '/relatorioOrientacao.json') :
			file_get_contents($analysisPath . '/advise.json');

		$file    = file_exists($analysisPath . '/relatorioProducaoBibiografica.json') ?
			file_get_contents($analysisPath . '/relatorioProducaoBibiografica.json') :
			file_get_contents($analysisPath . '/publication.json');

		$list    = file_get_contents($analysisPath . '/' . $id . '.list.json');

		$jsonProducoes   = json_decode($file, true);
		$jsonOrientacoes = json_decode($orient, true);
		$jsonPerfil      = json_decode($profile, true);
		$jsonList        = json_decode($list, true);

		$results = array();
		$article = 0;
		$books = 0;
		$orientations = 0;
		foreach ($jsonPerfil as $id => $r) {
			$member = array('member' => array(), 'articles' => array(), 'books' => array(), 'orientations' => array());
			if (count($r['producao_bibiografica'])) {
				foreach ($r['producao_bibiografica'] as $key => $d) {
					foreach ($d as $k2 => $data) {

						//var_dump($key, $data, "<br /> <hr /><hr />");

						$s = $this->customSearch($q, $data);
						if (null != $s) {
							if ($key == 'CAPITULO_DE_LIVRO' || $key == 'LIVRO') {
								array_push($member['books'], array($key => $s));
							} else {
								array_push($member['articles'], array($key => $s));
							}

							break;
						}
					}
				}
			}

			if (count($r['orientacoes_academicas'])) {

				foreach ($r['orientacoes_academicas'] as $key => $d) {
					foreach ($d as $k2 => $data) {

						//var_dump($key, $data, "<br /> <hr /><hr />");

						$s = $this->customSearch($q, $data);

						if (null != $s) {
							array_push($member['orientations'], array($key => $s));
							break;
						}
					}
				}
			}

			if (stristr($r['resumo_cv'], $q) || count($member['orientations']) || count($member['articles']) || count($member['books'])) {
				$member['member']['name'] = $r['nome'];
				$member['member']['resume'] = $r['resumo_cv'];
				$member['member']['id'] = $id;
				$member['member']['seniority'] = $r['senioridade'];
				array_push($results, $member);
			}
		}
		//var_dump($results);
		$list = $jsonList ? reset($jsonList) : null;

		$settings = $this->getModel()->get('Application\Entity\Settings', 1);
		$this->layout()->setVariable('settings', $settings);

		$view->setVariables(
			array(
				'analysis' => $analysis,
				'members'  => $results,
				'q' => $q
			)
		);
		return $view;
	}

	public function checkXmlFilesAction()
	{

		if ($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			$ids = explode(';', $data['data']);
			$r = array();
			$settings = $this->getModel()->get('Application\Entity\Settings', 1);

			foreach ($ids as $id) {
				$r[$id] = file_exists($settings->getXmlpath() . '/' . $id . '.xml');
			}

			return new JsonModel(
				array(
					'data' => $r
				)
			);
		}
	}

	public function downloadXmlFilesAction()
	{

		$return  = null;

		if ($this->getRequest()->isPost()) {
			$post = $this->getRequest()->getPost();
			$r = array();
			$settings = $this->getModel()->get('Application\Entity\Settings', 1);

			$path = getcwd() . '/data/tmp/';
			$dirName = '_m_' . time();

			$filePath = $path . $dirName;

			//cria o diretório da análise
			if (!file_exists($filePath)) {
				mkdir($filePath, 0777, true);
			}

			chmod($filePath, 0777);

			$zip = new ZipArchive();
			$zipName = 'xml_pack_' . count($post['data'])  . '_' . 'files.zip';
			$filename = $filePath . '/' . $zipName;

			if ($zip->open($filename, ZipArchive::CREATE) !== TRUE) {

				exit("cannot open <$filename>\n");
			}

			foreach ($post['data'] as $researcher) {
				$name = $researcher['id'] . '.xml';
				$xml = $settings->getXmlpath() . '/' . $name;
				if (file_exists($xml)) {
					$zip->addFile($xml, $name);
				}
			}

			$return = base64_encode($dirName . '/' . $zipName);
		}

		return new JsonModel(
			array(
				'data' => $return
			)
		);
	}

	public function downloadXmlPackageAction()
	{

		$fileUrl = $this->getEvent()->getRouteMatch()->getParam('file', null);

		$path = getcwd() . '/data/tmp/';
		$filename = $path . base64_decode($fileUrl);
		//var_dump($filename);
		chmod($filename, 0777);

		try {
			$response = new \Zend\Http\Response\Stream();
			$fh = fopen($filename, 'rb');
			$response->setStream($fh);
			$response->setStatusCode(200);
			$response->setStreamName(basename($filename));
			$headers = new \Zend\Http\Headers();
			$headers->addHeaders(array(
				'Content-Disposition' => 'attachment; filename=' . basename($filename) . '',
				'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
				'Cache-Control' => 'public',
				//'Content-Description' => 'File Transfer',
				'Content-Type' => 'application/octet-stream',
				'Content-Transfer-Encoding' => 'binary',
				'Content-Length' => filesize($filename),
				'Expires' => '@0', // @0, because zf2 parses date as string to \DateTime() object
				'Pragma' => 'public'
			));

			$response->setHeaders($headers);
		} catch (\Exception $e) {
			var_dump($e);
			exit;
		}

		return $response;
	}

	public function downloadAction()
	{
		$reponse = true;

		$name = $this->getEvent()->getRouteMatch()->getParam('name', null);
		$id = $this->getEvent()->getRouteMatch()->getParam('id', null);
		$analysis = $this->getModel()->get('Application\Entity\Analysis', $id);

		$path = getcwd() . '/data/analyses/';
		$analysisPath = $path . $analysis->getId() . '/';

		if (file_exists($analysisPath . $name)) {
			$response = new \Zend\Http\Response\Stream();
			$response->setStream(fopen($analysisPath . $name, 'r'));
			$response->setStatusCode(200);
			$response->setStreamName(basename($analysisPath . $name));
			$headers = new \Zend\Http\Headers();
			$headers->addHeaders(array(
				'Content-Disposition' => 'attachment; filename="' . basename($analysisPath . $name) . '"',
				'Content-Type' => 'application/json',
				'Content-Length' => filesize($analysisPath . $name),
				'Expires' => '@0', // @0, because zf2 parses date as string to \DateTime() object
				'Cache-Control' => 'must-revalidate',
				'Pragma' => 'public'
			));
			$response->setHeaders($headers);
			return $response;
		}
		return new JsonModel(
			array(
				'data' => false
			)
		);
	}

	public function delFileAction()
	{
		$reponse = true;

		$name = $this->getRequest()->getPost('name');
		$id = $this->getRequest()->getPost('key');

		$analysis = $this->getModel()->get('Application\Entity\Analysis', $id);

		$path = getcwd() . '/data/analyses/';
		$analysisPath = $path . $analysis->getId() . '/';

		if (file_exists($analysisPath . $name)) {
			unlink($analysisPath . $name);
		}
		return new JsonModel(
			array(
				'data' => $reponse
			)
		);
	}
	public function upFilesAction()
	{

		$id = $this->getEvent()->getRouteMatch()->getParam('id', null);
		$analysis = $this->getModel()->get('Application\Entity\Analysis', $id);

		$uploaded = $this->getRequest()->getFiles();

		if (count($uploaded)) {

			$path = getcwd() . '/data/analyses/';
			$analysisPath = $path . $analysis->getId();
			$reponse = false;
			$files = $uploaded['files'];

			if (is_array($files)) {
				foreach ($files as $index => $file) {
					$name = $file['name'];
					// 					if((strrpos($name, 'list.json') === false  || strrpos($name, 'config.json') === false) && $name != $id . '.' . $name) {
					// 						$name = $id . '.' . $name;
					// 					}

					if (move_uploaded_file($file['tmp_name'], $analysisPath . '/' . $name)) {
						$reponse = true;
					}
				}
			}
			return new JsonModel(
				array(
					'data' => $reponse
				)
			);
		}
	}

	public function editAction()
	{
		$view = new ViewModel();
		$id = $this->getEvent()->getRouteMatch()->getParam('id', null);
		$analysis = $this->getModel()->get('Application\Entity\Analysis', $id);

		$path = getcwd() . '/data/analyses/';
		$analysisPath = $path . $analysis->getId();

		$profileExists = false;
		$listExists = false;

		$profile = null;
		if (file_exists($analysisPath . '/profile.json')) {
			$profile = file_get_contents($analysisPath . '/profile.json');
			$prodileExists = true;
			$profile  = json_decode($profile, true);
		}

		$list = null;
		if (file_exists($analysisPath . '/' . $id . '.list.json')) {
			$list    = file_get_contents($analysisPath . '/' . $id . '.list.json');
			$listExists = true;
			$list        = json_decode($list, true);
		}
		$settings = $this->getModel()->get('Application\Entity\Settings', 1);
		$this->layout()->setVariable('settings', $settings);
		$view->setVariables(
			array(
				'analysis' => $analysis,
				'members' => $profile,
				'list' => $listExists ? $list : $profile
			)
		);
		return $view;
	}

	public function profileAction()
	{
		$view = new ViewModel();
		$id = $this->getEvent()->getRouteMatch()->getParam('id', null);
		$memberId = $this->getEvent()->getRouteMatch()->getParam('member', null);
		$analysis = $this->getModel()->get('Application\Entity\Analysis', $id);

		$path = getcwd() . '/data/analyses/';
		$analysisPath = $path . $id;
		$profile = null;

		if (file_exists($analysisPath . '/profile.json')) {
			$profile = file_get_contents($analysisPath . '/profile.json');
		}

		$profile  = json_decode($profile, true);

		$member = isset($profile[$memberId]) ? $profile[$memberId] : null;

		$cv = null;
		$cvs = $this->getModel()->getBy(['lattes_id' => $memberId], 'Application\Entity\Cv');
		if ($cvs)
			$cv = reset($cvs);

		$settings = $this->getModel()->get('Application\Entity\Settings', 1);
		$this->layout()->setVariable('settings', $settings);

		$lastUpdate = $cv ? $cv->getLastUpdate() : null;

		if (strlen($lastUpdate) === 8) {
			$lastUpdate = preg_replace("/(\d{2})(\d{2})(\d{4})/", "\$1/\$2/\$3", $lastUpdate);
		}

		$member['updated'] = $cv ? $lastUpdate : 'Não existente';

		$view->setVariables(
			array(
				'analysis' => $analysis,
				'member' => $member ? $member : null,
				'members' => $profile,
				'memberId' => $memberId,
				'settings' => $settings
			)
		);

		return $view;
	}

	public function stopAction()
	{
		$view = new ViewModel();
		$pid = $this->getEvent()->getRouteMatch()->getParam('pid', null);

		$plugin = new ScriptExec();
		$plugin->setPid($pid);
		$plugin->stop();

		return new JsonModel(
			array(
				'data' => $plugin->status()
			)
		);
	}

	public function graphAction()
	{
		$view = new ViewModel();
		$id = $this->getEvent()->getRouteMatch()->getParam('id', null);
		$analysis = $this->getModel()->get('Application\Entity\Analysis', $id);

		$path = getcwd() . '/data/analyses/';
		$analysisPath = $path . $analysis->getId();

		$file    = file_exists($analysisPath . '/graph.json') ?
			file_get_contents($analysisPath . '/graph.json') :
			null;
		$jsonGraph   = json_decode($file, true);

		$json = array();

		$json['label'] = 'Metadados';
		$json['nodes'] = array();
		foreach ($jsonGraph['nodes'] as $node) {
			$node['label'] = $node['properties']['name'];
			array_push($json['nodes'], $node);
		}

		$json['links'] = $jsonGraph['links'];

		return new JsonModel($json);
	}

	public function logAction()
	{
		$view = new ViewModel();
		$id = $this->getEvent()->getRouteMatch()->getParam('id', null);

		$analysis = $this->getModel()->get('Application\Entity\Analysis', $id);

		$path = getcwd() . '/data/analyses/';
		$analysisPath = $path . $analysis->getId();

		$logText = 'Conteúdo do log insdisponível.';

		if (file_exists($analysisPath . '/' . $id . '.log'))
			$logText = file_get_contents($analysisPath . '/' . $id . '.log');

		return new JsonModel(
			array(
				'data' => "<pre>{$logText}</pre>"
			)
		);
	}

	private function restart($analysis)
	{

		$path = getcwd() . '/data/analyses/';
		$id = $analysis->getId();
		$analysisPath = $path . $id;
		$scriptPath = getcwd() . '/data/R_script';


		if (file_exists($analysisPath . '/perfis.json'))
			unlink($analysisPath . '/perfis.json');

		if (file_exists($analysisPath . '/profile.json'))
			unlink($analysisPath . '/profile.json');

		if (file_exists($analysisPath . '/relatorioOrientacao.json'))
			unlink($analysisPath . '/relatorioOrientacao.json');

		if (file_exists($analysisPath . '/advise.json'))
			unlink($analysisPath . '/advise.json');

		if (file_exists($analysisPath . '/relatorioProducaoBibiografica.json'))
			unlink($analysisPath . '/relatorioProducaoBibiografica.json');

		if (file_exists($analysisPath . '/publication.json'))
			unlink($analysisPath . '/publication.json');

		if (file_exists($analysisPath . '/graph.json'))
			unlink($analysisPath . '/graph.json');

		if (file_exists($analysisPath . '/' . $id . '.log'))
			unlink($analysisPath . '/' . $id . '.log');

		$cnfName = $analysisPath . '/' . $id . '.config.json';

		//escreve arquivo de log para a análise
		$logFile = $analysisPath . '/' . $id . '.log';
		file_put_contents($logFile, '');
		chmod($logFile, 0777);

		$linkR = ENVIRONMENT == 'production' ? '/usr/bin/Rscript' : '/usr/local/bin/Rscript';
		//inicia o script da analise
		$command = "{$linkR} {$scriptPath}/App.R -'{$cnfName}'";

		$plugin = new ScriptExec($command, $logFile);

		$analysis->setPID($plugin->getPid())->setStatus(Analysis::RUNNING)->setUpdated(new \DateTime('now'));

		//salva a análise
		$this->getModel()->save($analysis);

		return $plugin;
	}

	public function howManyUpdatesAction()
	{
		exec('ps aux | grep App.R | wc -l', $op);
		return $op;
	}

	public function restartAction()
	{
		$view = new ViewModel();
		$id = $this->getEvent()->getRouteMatch()->getParam('id', null);

		$analysis = $this->getModel()->get('Application\Entity\Analysis', $id);

		$plugin = $this->restart($analysis);

		return new JsonModel(
			array(
				'data' => $plugin->status()
			)
		);
	}

	public function startAction()
	{
		$view = new ViewModel();
		$id = $this->getEvent()->getRouteMatch()->getParam('id', null);
		$token = $this->getEvent()->getRouteMatch()->getParam('token', null);

		if ($token !== "7A1E2893-6BD0-4F05-916B-4A54144F6134")
			return new JsonModel(
				array(
					'auth' => array("status" => false, "msg" => "Acesso não autorizado")
				)
			);

		$analysis = $this->getModel()->get('Application\Entity\Analysis', $id);

		if (!$analysis)
			return new JsonModel(
				array(
					'auth' => array("status" => false, "msg" => "Análise não existe em nossa base dados. Favor, verificar se o código de identificação está correto")
				)
			);

		$plugin = $this->restart($analysis);

		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}

		$users = $this->getModel()->getBy(array('username' => 'brunosfweb'), 'Application\Entity\User');
		$user = reset($users);
		$log = new Log();
		$log->setCreated(new \DateTime('now'))
			->setModule('Analysis\Api\Integration')
			->setOrigin($ip)
			->setCommand('Start Analysis')->setDetails('Analysis  (' . $analysis->getId() . ') : ' . $analysis->getTitle())
			->setUser($user);

		$this->getModel()->save($log);

		return new JsonModel(
			array(
				'data' => $plugin->status()
			)
		);
	}

	public function statusAction()
	{
		$analyses = $this->getModel()->getBy(array('status' => array(Analysis::RUNNING, Analysis::WAITING)), 'Application\Entity\Analysis');
		$return  = array();
		foreach ($analyses as $analysis) {
			$status = $analysis->getStatus();

			$plugin = new ScriptExec();
			$plugin->setPid($analysis->getPID());

			$path = getcwd() . '/data/analyses/';
			$analysisPath = $path . $analysis->getId();

			$filesSuccefullCreated = false;

			if ((file_exists($analysisPath . '/profile.json')) &&
				(file_exists($analysisPath . '/advise.json')) &&
				(file_exists($analysisPath . '/publication.json')) &&
				(file_exists($analysisPath . '/graph.json'))
			) {

				$filesSuccefullCreated = true;
				$status = Analysis::READY;
			}

			if ($plugin->status() === true  && $status == Analysis::FAILED || $plugin->status() === true  && $status == Analysis::READY) {
				$status = Analysis::RUNNING;
			} else if ($plugin->status() === false  && $status == Analysis::RUNNING && !$filesSuccefullCreated) {
				$status = Analysis::FAILED;
			} else if ($status == Analysis::WAITING) {

				if (
					$analysis->getRequests()->first()
					&& $analysis->getRequests()->first()->getStarted()
					&& !count($analysis->getRequests()->first()->getActiveJobs())
				) {

					$this->restart($analysis);
					$status = Analysis::RUNNING;
				} else {

					$toStart = true;
					$list = json_decode($analysis->getList(), true);
					$settings = $this->getModel()->get('Application\Entity\Settings', 1);
					$pathXmls = $settings->getXmlpath();
					var_dump($list);
					foreach ($list['fiocruz'] as $arr) {
						if (!file_exists($pathXmls . '/' . $arr['id'] . '.xml')) {
							$toStart = false;
						}
					}
					if ($toStart) {
						$this->restart($analysis);
						$status = Analysis::RUNNING;
					} else {
						$status = array(Analysis::WAITING, $analysis->getRequests()->first()->getTotal() - count($analysis->getRequests()->first()->getActiveJobs()));
					}
				}
			}

			if ($filesSuccefullCreated) {
				$status = Analysis::READY;
			}

			$return[$analysis->getId()] = $status;
		}



		return new JsonModel(
			array(
				'data' => $return
			)
		);
	}

	public function cadAction()
	{
		$view = new ViewModel();

		$lattesIds = array();

		$path = getcwd() . '/data/';

		$file = file_get_contents($path . 'researches.json');
		$r = json_decode($file);
		foreach ($r as $rs) {
			$line = strpos($rs->lattes, 'br/');
			$id = substr($rs->lattes, $line + 3);
			//var_dump($id);

			$e = new Researcher();
			$e->setName($rs->name)
				->setLattesUrl($rs->lattes)
				->setLattesId($id)
				->setDegree($rs->degree)
				->setExpertise($rs->expertise)
				->setDepartment($rs->department);
			$this->getModel()->save($e);
		}
		print_r(count($r));
		exit;
		$view->setTerminal(true);
	}

	public function createAction()
	{
		$view = new ViewModel();

		$scriptPath = getcwd() . '/data/R_script';
		$cnfName = '/usr/local/zend/apache2/htdocs/research_analytics/data/analyses/57/57.config.json';

		//$command = "Rscript /usr/local/zend/apache2/htdocs/research_analytics/data/analyses/52/my_script.R";
		$command = "/usr/local/bin/Rscript {$scriptPath}/App.R -'{$cnfName}'";
		//	$command = 'nohup '.$command.' > /dev/null 2> &1 & echo $!';
		//$r = exec($command, $op);
		//var_dump($r, $op);

		//$plugin = new ScriptExec();
		$plugin = new ScriptExec($command);
		//$plugin->setPid(76176570);
		//$pid->setPid($pid);
		//var_dump($plugin->getPid());
		//var_dump($plugin->getPid());

		var_dump($plugin->status());


		//var_dump($plugin->start());

		$view->setTerminal(true);
		exit;
	}

	public function newAction()
	{
		if ($this->authenticationPlugin()->getIdentity()->profile->id > \Application\Entity\Profile::EMPLOYEE)
			return $this->redirect()->toRoute('default', array(
				'controller' => 'index',
				'action' =>  'no-access'
			));

		$settings = $this->getModel()->get('Application\Entity\Settings', 1);
		$this->layout()->setVariable('settings', $settings);
		$view = new ViewModel(array('settings' => $settings));
		return $view;
	}
	/**
	 * CSV Membros de uma unidade
	 */
	public function csvMembersAction()
	{
		$id = $this->getEvent()->getRouteMatch()->getParam('analysis', null);
		$analysis = $this->getModel()->get('Application\Entity\Analysis', $id);
		$settings = $this->getModel()->get('Application\Entity\Settings', 1);
		$path = getcwd() . '/data/analyses/';
		$analysisPath = $path . $id;

		if (!file_exists($analysisPath)) {
			mkdir($analysisPath, 0777, true);
		}

		chmod($analysisPath, 0777);
		$tmpPath = getcwd() . '/data/tmp/' . $id;

		$profile = file_exists($analysisPath . '/perfis.json') ? file_get_contents($analysisPath . '/perfis.json') : file_get_contents($analysisPath . '/profile.json');
		$profile  = json_decode($profile, true);

		$allProductions = array();
		$allOrientations = array();

		$adviseCsvFile = $tmpPath . '_all_advises_by_members.csv';
		$fpAdvise = fopen($adviseCsvFile, 'w');

		fputcsv($fpAdvise, array(
			'index',
			'ano',
			'natureza',
			'titulo',
			'id_lattes_orientador',
			'nome_orientador',
			'id_lattes_aluno',
			'nome_aluno',
			'instituicao',
			'curso',
			'codigo_do_curso',
			'bolsa',
			'agencia_financiadora',
			'codigo_agencia_financiadora'
		));

		$publicationCsvFile = $tmpPath . '_all_publication_by_members.csv';
		$fpPublication = fopen($publicationCsvFile, 'w');

		fputcsv($fpPublication, array(
			'index',
			'id_lattes',
			'nome',
			'ano',
			'tipo',
			'natureza',
			'titulo',
			'capitulo',
			'editora',
			'periodico',
			'isbn',
			'doi',
			'issn',
			'classificacao',
			'pais',
			'cidade',
			'qualis',
			'pontos',
		));

		$i = 0;
		$j = 0;
		$k = 0;
		$total = 0;
		foreach ($profile as $idLattes => $data) {

			$name = $data['nome'];
			$productions = $data['producao_bibiografica'];
			$orientations = $data['orientacoes_academicas'];

			$total += count($orientations);

			$subt = 0;

			//echo "{$k} -> [[{$name}]]";

			foreach ($orientations as $t => $inOrientations) {

				//echo "<br /> ---> " . $t ;

				foreach ($inOrientations as $info) {
					$arrOrient = array(
						'index' => $i++,
						'ano' => $info['ano'],
						'natureza' => $info['natureza'],
						'titulo' => $info['titulo'],
						'id_lattes_orientador' => $idLattes,
						'nome_orientador' => $name,
						'id_lattes_aluno' => $info['id_lattes_aluno'],
						'nome_aluno' => $info['nome_aluno'],
						'instituicao' => $info['instituicao'],
						'curso' => $info['curso'],
						'codigo_do_curso' => $info['codigo_do_curso'],
						'bolsa' => $info['bolsa'],
						'agencia_financiadora' => $info['agencia_financiadora'],
						'codigo_agencia_financiadora' => $info['codigo_agencia_financiadora']
					);
					//echo $arrOrient['titulo'] . " ({$arrOrient['nome_aluno']} - {$arrOrient['ano']}) <br />";
					$subt++;
					fputcsv($fpAdvise, $arrOrient);
					array_push($allOrientations, $arrOrient);
				}
			}



			foreach ($productions as $type => $inProductions) {
				foreach ($inProductions as $info) {

					$arrProd = array(
						'index' => $j++,
						'id_lattes' => $idLattes,
						'nome' => $name,
						'ano' => '',
						'tipo' => '',
						'natureza' => '',
						'titulo' => '',
						'capitulo' => '',
						'editora' => '',
						'periodico' => '',
						'isbn' => '',
						'doi' => '',
						'issn' => '',
						'classificacao' => '',
						'pais' => '',
						'cidade' => '',
						'qualis' => 'NP',
						'pontos' => 0,
					);

					switch ($type) {

						case 'CAPITULO_DE_LIVRO':
							$arrProd['tipo'] = 'Capítulo de Livro';
							$arrProd['titulo'] = $info['titulo_do_livro'];
							$arrProd['capitulo'] = $info['titulo_do_capitulo'];
							$arrProd['editora'] = $info['nome_da_editora'];
							$arrProd['isbn'] = $info['isbn'];
							$arrProd['doi'] = $info['doi'];
							$arrProd['ano'] = $info['ano'];
							break;

						case 'LIVRO':
							$arrProd['titulo'] = $info['titulo'];
							$arrProd['editora'] = $info['nome_da_editora'];
							$arrProd['tipo'] = 'Livro';
							$arrProd['isbn'] = $info['isbn'];
							$arrProd['doi'] = $info['doi'];
							$arrProd['ano'] = $info['ano'];
							$arrProd['pais'] = $info['pais_de_publicacao'];
							break;

						case 'PERIODICO':
						case 'ARTIGO_ACEITO':
						case 'TEXTO_EM_JORNAIS':
							$arrProd['tipo'] = 'Periódico';
							$arrProd['natureza'] = $info['natureza'];
							$arrProd['titulo'] = $info['titulo'];
							$arrProd['periodico'] = $info['periodico'];
							$arrProd['issn'] = $info['issn'];
							$arrProd['ano'] = $info['ano'];


							if ($settings->getQualis()) {
								if (isset($info['issn'])) {
									$issn = preg_replace("/(\d{4})(\d{4})/", "\$1-\$2", $info['issn']);
									$qualisInfo = $this->getModel()->getBy(array('issn' => $issn), 'Application\Entity\QualisIssnCapes');
									$qualisInfo = is_array($qualisInfo) ? reset($qualisInfo) : $qualisInfo;

									if ($qualisInfo) {
										$arrProd['qualis'] = $qualisInfo->getClassification();
										$arrProd['pontos'] = number_format($qualisInfo->getQualisInfo()->getPoints(), 2, ',', '.');
									}
								}
							}

							break;

						case 'EVENTO':
							$arrProd['tipo'] = 'Evento';
							$arrProd['natureza'] = $info['natureza'];
							$arrProd['titulo'] = $info['titulo'];
							$arrProd['periodico'] = $info['nome_do_evento'];
							$arrProd['ano'] = $info['ano_do_trabalho'];
							$arrProd['classificacao'] = $info['classificacao'];
							$arrProd['pais'] = $info['pais_do_evento'];
							$arrProd['cidade'] = $info['cidade_do_evento'];
							$arrProd['doi'] = $info['doi'];

							if ($settings->getQualis()) {
								if (isset($info['issn'])) {
									$issn = preg_replace("/(\d{4})(\d{4})/", "\$1-\$2", $info['issn']);
									$qualisInfo = $this->getModel()->getBy(array('issn' => $issn), 'Application\Entity\QualisIssnCapes');
									$qualisInfo = is_array($qualisInfo) ? reset($qualisInfo) : $qualisInfo;

									if ($qualisInfo) {
										$arrProd['qualis'] = $qualisInfo->getClassification();
										$arrProd['pontos'] = number_format($qualisInfo->getQualisInfo()->getPoints(), 2, ',', '.');
									}
								}
							}

							break;

						default:
							break;
					}
					array_push($allProductions, $arrProd);
					fputcsv($fpPublication, $arrProd);
				}
			}

			$k++;
			//echo "<br /> subtotal : " . $subt . "<br />";
		}
		//echo '<br /> total : ';
		//echo $total;

		fclose($fpAdvise);
		fclose($fpPublication);

		$response = new \Zend\Http\Response\Stream();

		try {

			$zip = new ZipArchive();

			$zipfilename = $analysisPath . '/' . $id . '.all_members.zip';
			$pubFile = file_get_contents($publicationCsvFile);
			$advFile = file_get_contents($adviseCsvFile);

			if ($zip->open($zipfilename, ZipArchive::CREATE) !== TRUE) {

				exit("cannot open <$zipfilename>\n");
			}

			if (file_exists($adviseCsvFile)) {
				$zip->addFile($adviseCsvFile, 'all_advises.csv');
			}

			if (file_exists($publicationCsvFile)) {
				$zip->addFile($publicationCsvFile, 'all_publications.csv');
			}

			$zip->close();

			$fh = fopen($zipfilename, 'rb');
			$response->setStream($fh);
			$response->setStatusCode(200);
			$response->setStreamName(basename($zipfilename));

			$headers = new \Zend\Http\Headers();
			$headers->addHeaders(array(
				'Content-Disposition' => 'attachment; filename=' . basename($zipfilename) . '',
				'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
				'Cache-Control' => 'public',
				//'Content-Description' => 'File Transfer',
				'Content-Type' => 'application/octet-stream',
				'Content-Transfer-Encoding' => 'binary',
				'Content-Length' => filesize($zipfilename),
				'Expires' => '@0', // @0, because zf2 parses date as string to \DateTime() object
				'Pragma' => 'public'
			));

			$response->setHeaders($headers);
		} catch (\Exception $e) {
			var_dump($e);
			exit;
		}
		return $response;
		//return new JsonModel($allOrientations);
	}

	private function tirarAcentos($string)
	{
		$str = preg_replace(array("/(á|à|ã|â|ä)/", "/(Á|À|Ã|Â|Ä)/", "/(é|è|ê|ë)/", "/(É|È|Ê|Ë)/", "/(í|ì|î|ï)/", "/(Í|Ì|Î|Ï)/", "/(ó|ò|õ|ô|ö)/", "/(Ó|Ò|Õ|Ô|Ö)/", "/(ú|ù|û|ü)/", "/(Ú|Ù|Û|Ü)/", "/(ñ)/", "/(Ñ)/"), explode(" ", "a A e E i I o O u U n N"), $string);
		return strtolower(str_replace(' ', '', $str));
	}

	public function viewAction()
	{
		$settings = $this->getModel()->get('Application\Entity\Settings', 1);
		$this->layout()->setVariable('settings', $settings);
		$view = new ViewModel();
		return $view;
	}

	public function viewMembersAction()
	{
		$view = new ViewModel();
		$id = $this->getEvent()->getRouteMatch()->getParam('id', null);
		$analysis = $this->getModel()->get('Application\Entity\Analysis', $id);
		$path = getcwd() . '/data/analyses/';
		$analysisPath = $path . $id;
		$list    = file_get_contents($analysisPath . '/' . $id . '.list.json');
		$profile = file_exists($analysisPath . '/perfis.json') ? file_get_contents($analysisPath . '/perfis.json') : file_get_contents($analysisPath . '/profile.json');
		$list        = json_decode($list, true);
		$profile  = json_decode($profile, true);

		$chart = [];
		$areas = [];
		$total = 0;

		foreach ($profile as $id => $p) {
			$added = false;
			foreach ($p['areas_de_atuacao'] as $d) {
				if (isset($d['grande_area'])  && $d['grande_area'] != "") {
					if (!isset($areas[$d['grande_area']]))
						$areas[$d['grande_area']] = [];

					if (array_search($id, $areas[$d['grande_area']]) === false)
						array_push($areas[$d['grande_area']], $id);

					if (!$added) {
						$total++;
						$added = true;
					}
				}
			}
		}
		$settings = $this->getModel()->get('Application\Entity\Settings', 1);
		$this->layout()->setVariable('settings', $settings);

		$view->setVariables(
			array(
				'analysis' => $analysis,
				'members' => $profile,
				'chart' => $areas,
				'totalChart' => $total,
				'list' => $list,
				'settings' => $settings
			)
		);
		return $view;
	}

	public function viewPublicationAction()
	{
		$view = new ViewModel();
		$settings = $this->getModel()->get('Application\Entity\Settings', 1);
		$this->layout()->setVariable('settings', $settings);

		$id = $this->getEvent()->getRouteMatch()->getParam('id', null);
		$analysis = $this->getModel()->get('Application\Entity\Analysis', $id);
		$path = getcwd() . '/data/analyses/';
		$analysisPath = $path . $id;

		$file    = file_exists($analysisPath . '/relatorioProducaoBibiografica.json') ?
			file_get_contents($analysisPath . '/relatorioProducaoBibiografica.json') :
			file_get_contents($analysisPath . '/publication.json');

		$jsonProducoes   = json_decode($file, true);

		$chartPublications = array();

		$chartPublications['Bibliografica'] = [];
		$totalQualis = 0;
		$totalArticles = 0;
		$chartPublications['Type'] = [];

		if (isset($jsonProducoes['PERIODICO'])) {
			foreach ($jsonProducoes['PERIODICO'] as $ano => $dados) {

				if (!isset($chartPublications['Bibliografica'][$ano]))
					$chartPublications['Bibliografica'][$ano] = 0;

				$chartPublications['Bibliografica'][$ano] = count($dados);

				foreach ($dados as $d) {
					//var_dump('periodico',$d['natureza']);
					if (!isset($chartPublications['Type'][$d['natureza']]))
						$chartPublications['Type'][$d['natureza']] = 0;

					$chartPublications['Type'][$d['natureza']] = ++$chartPublications['Type'][$d['natureza']];

					$totalArticles++;
				}
			}
		}

		$totalEvents = 0;

		if (isset($jsonProducoes['EVENTO'])) {
			foreach ($jsonProducoes['EVENTO'] as $ano => $dados) {

				if (!isset($chartPublications['Bibliografica'][$ano]))
					$chartPublications['Bibliografica'][$ano] = 0;

				$chartPublications['Bibliografica'][$ano] += count($dados);

				foreach ($dados as $d) {
					//var_dump('evento',$d['natureza']);
					if (!isset($chartPublications['Type'][$d['natureza']]))
						$chartPublications['Type'][$d['natureza']] = 0;

					$chartPublications['Type'][$d['natureza']] = ++$chartPublications['Type'][$d['natureza']];

					$totalEvents++;
				}
			}
		}
		$totalOthers = 0;
		if (isset($jsonProducoes['DEMAIS_TIPOS_DE_PRODUCAO_BIBLIOGRAFICA'])) {
			foreach ($jsonProducoes['DEMAIS_TIPOS_DE_PRODUCAO_BIBLIOGRAFICA'] as $ano => $dados) {

				if (!isset($chartPublications['Bibliografica'][$ano]))
					$chartPublications['Bibliografica'][$ano] = 0;

				$chartPublications['Bibliografica'][$ano] += count($dados);

				foreach ($dados as $d) {

					if (!isset($chartPublications['Type'][$d['natureza']]))
						$chartPublications['Type'][$d['natureza']] = 0;

					$chartPublications['Type'][$d['natureza']] = ++$chartPublications['Type'][$d['natureza']];

					$totalOthers++;
				}
			}
		}

		$totalArticles = 0;
		if (isset($jsonProducoes['ARTIGO_ACEITO'])) {
			foreach ($jsonProducoes['ARTIGO_ACEITO'] as $ano => $dados) {

				if (!isset($chartPublications['Bibliografica'][$ano]))
					$chartPublications['Bibliografica'][$ano] = 0;

				$chartPublications['Bibliografica'][$ano] += count($dados);

				foreach ($dados as $d) {

					if (!isset($chartPublications['Type'][$d['natureza']]))
						$chartPublications['Type'][$d['natureza']] = 0;

					$chartPublications['Type'][$d['natureza']] = ++$chartPublications['Type'][$d['natureza']];

					$totalArticles++;
				}
			}
		}

		$totalJournals = 0;
		if (isset($jsonProducoes['TEXTO_EM_JORNAIS'])) {
			foreach ($jsonProducoes['TEXTO_EM_JORNAIS'] as $ano => $dados) {

				if (!isset($chartPublications['Bibliografica'][$ano]))
					$chartPublications['Bibliografica'][$ano] = 0;

				$chartPublications['Bibliografica'][$ano] += count($dados);

				foreach ($dados as $d) {

					if (!isset($chartPublications['Type'][$d['natureza']]))
						$chartPublications['Type'][$d['natureza']] = 0;

					$chartPublications['Type'][$d['natureza']] = ++$chartPublications['Type'][$d['natureza']];

					$totalJournals++;
				}
			}
		}

		$totalBooks = 0;
		if (isset($jsonProducoes['LIVRO'])) {
			foreach ($jsonProducoes['LIVRO'] as $ano => $dados) {

				if (!isset($chartPublications['Bibliografica'][$ano]))
					$chartPublications['Bibliografica'][$ano] = 0;

				$chartPublications['Bibliografica'][$ano] += count($dados);

				foreach ($dados as $d) {

					if (!isset($chartPublications['Type'][$d['natureza']]))
						$chartPublications['Type'][$d['natureza']] = 0;

					$chartPublications['Type'][$d['natureza']] = ++$chartPublications['Type'][$d['natureza']];

					$totalBooks++;
				}
			}
		}


		$totalChapters = 0;

		if (isset($jsonProducoes['CAPITULO_DE_LIVRO'])) {
			foreach ($jsonProducoes['CAPITULO_DE_LIVRO'] as $ano => $dados) {
				if (!isset($chartPublications['Bibliografica'][$ano]))
					$chartPublications['Bibliografica'][$ano] = 0;

				$chartPublications['Bibliografica'][$ano] += count($dados);

				foreach ($dados as $d) {

					//var_dump('livro',$d['tipo']);

					if (!isset($chartPublications['Type'][$d['tipo']]))
						$chartPublications['Type'][$d['tipo']] = 0;

					$chartPublications['Type'][$d['tipo']] = ++$chartPublications['Type'][$d['tipo']];

					$totalChapters++;
				}
			}
		}

		$view->setVariables(
			array(
				'analysis' => $analysis,
				'articles' => $jsonProducoes,
				'chartPublication' => $chartPublications,
				'settings' => $settings,

			)
		);
		return $view;
	}

	public function viewArticlesAction()
	{
		$view = new ViewModel();
		$id = $this->getEvent()->getRouteMatch()->getParam('id', null);
		$analysis = $this->getModel()->get('Application\Entity\Analysis', $id);

		$path = getcwd() . '/data/analyses/';
		$analysisPath = $path . $id;
		$file    = file_exists($analysisPath . '/relatorioProducaoBibiografica.json') ?
			file_get_contents($analysisPath . '/relatorioProducaoBibiografica.json') :
			file_get_contents($analysisPath . '/publication.json');

		$jsonProducoes   = json_decode($file, true);
		$settings = $this->getModel()->get('Application\Entity\Settings', 1);
		$this->layout()->setVariable('settings', $settings);

		$view->setVariables(
			array(
				'analysis' => $analysis,
				'articles' => $jsonProducoes,
				'settings' => $settings
			)
		);
		return $view;
	}

	public function viewBooksAction()
	{
		$view = new ViewModel();
		$id = $this->getEvent()->getRouteMatch()->getParam('id', null);
		$analysis = $this->getModel()->get('Application\Entity\Analysis', $id);

		$path = getcwd() . '/data/analyses/';
		$analysisPath = $path . $id;

		$file    = file_exists($analysisPath . '/relatorioProducaoBibiografica.json') ?
			file_get_contents($analysisPath . '/relatorioProducaoBibiografica.json') :
			file_get_contents($analysisPath . '/publication.json');

		$jsonProducoes   = json_decode($file, true);

		$settings = $this->getModel()->get('Application\Entity\Settings', 1);
		$this->layout()->setVariable('settings', $settings);

		$view->setVariables(
			array(
				'analysis' => $analysis,
				'articles' => $jsonProducoes['LIVRO'],
			)
		);
		return $view;
	}

	public function viewChaptersAction()
	{
		$view = new ViewModel();
		$id = $this->getEvent()->getRouteMatch()->getParam('id', null);
		$analysis = $this->getModel()->get('Application\Entity\Analysis', $id);

		$path = getcwd() . '/data/analyses/';
		$analysisPath = $path . $id;

		$file    = file_exists($analysisPath . '/relatorioProducaoBibiografica.json') ?
			file_get_contents($analysisPath . '/relatorioProducaoBibiografica.json') :
			file_get_contents($analysisPath . '/publication.json');

		$jsonProducoes   = json_decode($file, true);

		$settings = $this->getModel()->get('Application\Entity\Settings', 1);
		$this->layout()->setVariable('settings', $settings);


		$view->setVariables(
			array(
				'analysis' => $analysis,
				'chapters' => $jsonProducoes,
				'settings' => $settings
			)
		);
		return $view;
	}

	public function viewEventsAction()
	{
		$view = new ViewModel();
		$id = $this->getEvent()->getRouteMatch()->getParam('id', null);
		$analysis = $this->getModel()->get('Application\Entity\Analysis', $id);

		$path = getcwd() . '/data/analyses/';
		$analysisPath = $path . $id;
		$file    = file_exists($analysisPath . '/relatorioProducaoBibiografica.json') ?
			file_get_contents($analysisPath . '/relatorioProducaoBibiografica.json') :
			file_get_contents($analysisPath . '/publication.json');

		$jsonProducoes   = json_decode($file, true);
		$settings = $this->getModel()->get('Application\Entity\Settings', 1);
		$this->layout()->setVariable('settings', $settings);
		$view->setVariables(
			array(
				'analysis' => $analysis,
				'settings' => $settings,
				'events' => isset($jsonProducoes['EVENTO']) ? $jsonProducoes['EVENTO'] : array(),
			)
		);
		return $view;
	}

	public function viewNewspapersAction()
	{
		$view = new ViewModel();
		$id = $this->getEvent()->getRouteMatch()->getParam('id', null);
		$analysis = $this->getModel()->get('Application\Entity\Analysis', $id);

		$path = getcwd() . '/data/analyses/';
		$analysisPath = $path . $id;
		$file    = file_exists($analysisPath . '/relatorioProducaoBibiografica.json') ?
			file_get_contents($analysisPath . '/relatorioProducaoBibiografica.json') :
			file_get_contents($analysisPath . '/publication.json');

		$jsonProducoes   = json_decode($file, true);
		$settings = $this->getModel()->get('Application\Entity\Settings', 1);
		$this->layout()->setVariable('settings', $settings);
		$view->setVariables(
			array(
				'analysis' => $analysis,
				'newspapers' => isset($jsonProducoes['TEXTO_EM_JORNAIS']) ? $jsonProducoes['TEXTO_EM_JORNAIS'] : array(),
			)
		);
		return $view;
	}

	public function viewOrientationAction()
	{
		$view = new ViewModel();
		$id = $this->getEvent()->getRouteMatch()->getParam('id', null);
		$analysis = $this->getModel()->get('Application\Entity\Analysis', $id);

		$path = getcwd() . '/data/analyses/';
		$analysisPath = $path . $id;
		$file    = file_exists($analysisPath . '/relatorioOrientacao.json') ?
			file_get_contents($analysisPath . '/relatorioOrientacao.json') :
			file_get_contents($analysisPath . '/advise.json');

		$jsonOrientacoes   = json_decode($file, true);

		$totalOrientations = 0;
		$totalOrientationsInProgress = 0;
		$chartOrientation = array();
		$chartOrientationInProgress = array();

		foreach ($jsonOrientacoes as $type => $dataType) {

			if (
				strpos($type, '_CONCLUIDA_') !== false
				|| strpos($type, '_CONCLUIDAS') !== false
				|| strpos($type, '_CONCLUSAO_') !== false
			) {
				foreach ($dataType as $year => $d) {
					if (!isset($chartOrientation[$year]))
						$chartOrientation[$year] = 0;

					$chartOrientation[$year] += count($d);
					$totalOrientations += count($d);
				}
				continue;
			}

			if (
				strpos($type, '_EM_ANDAMENTO_') !== false
				|| strpos($type, 'INICIACAO_CIENTIFICA') !== false
				|| strpos($type, 'ORIENTACAO-DE-OUTRA-NATUREZA') !== false
			) {
				foreach ($dataType as $year => $d2) {

					if (!isset($chartOrientationInProgress[$year]))
						$chartOrientationInProgress[$year] = 0;

					$chartOrientationInProgress[$year] += count($d2);
					$totalOrientationsInProgress += count($d2);
				}
				continue;
			}
		}
		$settings = $this->getModel()->get('Application\Entity\Settings', 1);
		$this->layout()->setVariable('settings', $settings);
		$view->setVariables(
			array(
				'analysis' => $analysis,
				'orientations' => $jsonOrientacoes,
				'chartOrientation' => $chartOrientation,
				'chartOrientationInProgress' => $chartOrientationInProgress,
				't_orientations' => $totalOrientations,
				't_orientationsInProgress' => $totalOrientationsInProgress,
			)
		);
		return $view;
	}

	private function customSearch($keyword, $arrayToSearch)
	{

		foreach ($arrayToSearch as $key => $arrayItem) {
			if (is_array($arrayItem))
				continue;

			if (stristr($arrayItem, $keyword)) {
				return $key;
			}
		}
	}
}
