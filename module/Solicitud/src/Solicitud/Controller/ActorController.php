<?php

namespace Solicitud\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Solicitud\Service\Factory\Database as DatabaseAdapter;
use Zend\Db\Sql\Sql;
use Solicitud\Form\SecretariaSolicitudExtraordinario as SeSolicitudExtraordinarioForm;
use Solicitud\Form\RecepcionSolicitudExtraordinario as ReSolicitudExtraordinarioForm;
use Solicitud\Form\DecanoSolicitudExtraordinario as DiSolicitudExtraordinarioForm;
use Solicitud\Form\ResultadoRequisitos as ResultadoRequisitosForm;


class ActorController extends AbstractActionController
{
	public function recepcionAction()
	{
		$id_solicitud = $this->params()->fromRoute('id', 0);
		
		$database = new DatabaseAdapter(); //instanciamos la clase cuyo metodo nos devuelve el adaptador de nuestra bd
		$dbAdapter = $database->createService($this->getServiceLocator()); //llamamos al metodo que nos devuelve el adaptador de bd

		$form = new ReSolicitudExtraordinarioForm($dbAdapter);


		$sql = 	'SELECT s.solicitud as solicitud, mesa_entrada, fecha_solicitada, nombres, apellidos, carrera, telefono, email,
				asignatura, fecha_extraordinario, motivo, archivo, cumple_fecha, inscripto_tercera_op, ausente_tercera_op
				FROM usuarios as u INNER JOIN solicitudes as s ON (u.usuario = s.usuario_solicitante)
				INNER JOIN solicitud_de_extraordinario as se ON (s.solicitud = se.solicitud)
				INNER JOIN asignaturas_por_solicitud as aso ON (s.solicitud = aso.solicitud)
				LEFT OUTER  JOIN documentos_adjuntos as d ON (se.solicitud = d.solicitud)
				WHERE s.etapa_actual = \'RCDA\'
				ORDER BY mesa_entrada DESC
				LIMIT 1';
		// 		$sqlUsuarios = 'SELECT nombres, apellidos, telefono, email FROM usuarios ';
		// 		$sqlSolicitudEspecifica = 'SELECT fecha_extraordinario, motivo, cumple_fecha, ausente_tercera_op, inscripto_tercera_op FROM solicitud_de_extraordinario';

		$statement = $dbAdapter->query($sql);
		// 		$statementSolicitudEspecifica = $dbAdapter->query($sqlSolicitudEspecifica);
		// 		$statementUsuarios = $dbAdapter->query($sqlUsuarios);

		$result = $statement->execute();
		// 		$resultSolicitudEspecifica = $statementSolicitudEspecifica->execute();
		// 		$resultUsuarios  = $statementUsuarios->execute();

		// 			$arrayKeys = array_keys($res);
		// 			foreach ($arrayKeys as $key){
		// 				$res[$key]= $key.': '.$res[$key];

		$selectData = array();
		// 		$selectDataSolicitudes = array();
		// 		$selectDataSolicitudEspecifica = array();

		foreach ($result as $res) {

			$selectData[$res['solicitud']] = $res;// implode('<br>',$res);
		}

		$solicitud_id = $selectData[$res['solicitud']]['solicitud'];


		if($this->getRequest()->isPost()) {
			$data = array_merge_recursive(
					$this->getRequest()->getPost()->toArray(),
					// Notice: make certain to merge the Files also to the post data
					$this->getRequest()->getFiles()->toArray()
			);

			$form->setData($data);

			$sql2 = new Sql($dbAdapter);


			if (isset($data['Anular'])) {
				$update = $sql2->update('solicitudes')
				->set(array(
						'etapa_actual'	   => 'FINAL',
						'estado_solicitud' => 'ANUL',
				))
				->where(array('solicitud' => $solicitud_id));

				$statement = $sql2->prepareStatementForSqlObject($update);
				$results = $statement->execute();

			} else if (isset($data['Rechazar'])) {
				$update = $sql2->update('solicitudes')
				->set(array(
						'etapa_actual'	   => 'FINAL',
						'estado_solicitud' => 'RECHAZ',
				))
				->where(array('solicitud' => $solicitud_id));

				$statement = $sql2->prepareStatementForSqlObject($update);
				$results = $statement->execute();

			} else if (isset($data['VistoBueno'])) {
				$update = $sql2->update('solicitudes')
				->set(array(
						'etapa_actual'	   => 'DEL_SG',
						'estado_solicitud' => 'NUEVO',
				))
				->where(array('solicitud' => $solicitud_id));

				$statement = $sql2->prepareStatementForSqlObject($update);
				$results = $statement->execute();

			}

			$this->flashmessenger()->addSuccessMessage('Operación realizada con éxito');
			$this->flashmessenger()->addSuccessMessage(print_r($id_solicitud, TRUE));

			// redirect the user to the view user action
			return $this->redirect()->toRoute('user/default', array (
					'controller' => 'account',
					'action'     => 'me',
			));

		}

			//		$resultmerged = array_merge($resultUsuarios, $resultSolicitudes, $resultSolicitudEspecifica);
			return array('data' => $selectData, 'form1'=> $form);
	}


	public function secretariaAction()
	{
		$database = new DatabaseAdapter(); //instanciamos la clase cuyo metodo nos devuelve el adaptador de nuestra bd
		$dbAdapter = $database->createService($this->getServiceLocator()); //llamamos al metodo que nos devuelve el adaptador de bd

		$form = new SeSolicitudExtraordinarioForm($dbAdapter);


		$sql = 	'SELECT s.solicitud as solicitud, mesa_entrada, fecha_solicitada, nombres, apellidos,
				carrera, telefono, email, asignatura, fecha_extraordinario, motivo, archivo
				FROM usuarios as u INNER JOIN solicitudes as s ON (u.usuario = s.usuario_solicitante)
				INNER JOIN solicitud_de_extraordinario as se ON (s.solicitud = se.solicitud)
				INNER JOIN asignaturas_por_solicitud as aso ON (s.solicitud = aso.solicitud)
				LEFT OUTER JOIN documentos_adjuntos as d ON (se.solicitud = d.solicitud)
				WHERE s.etapa_actual = \'DEL_SG\'
				ORDER BY mesa_entrada DESC
				LIMIT 1';
// 		$sqlUsuarios = 'SELECT nombres, apellidos, telefono, email FROM usuarios ';
// 		$sqlSolicitudEspecifica = 'SELECT fecha_extraordinario, motivo, cumple_fecha, ausente_tercera_op, inscripto_tercera_op FROM solicitud_de_extraordinario';

		$statement = $dbAdapter->query($sql);
// 		$statementSolicitudEspecifica = $dbAdapter->query($sqlSolicitudEspecifica);
// 		$statementUsuarios = $dbAdapter->query($sqlUsuarios);

		$result = $statement->execute();
// 		$resultSolicitudEspecifica = $statementSolicitudEspecifica->execute();
// 		$resultUsuarios  = $statementUsuarios->execute();

		// 			$arrayKeys = array_keys($res);
		// 			foreach ($arrayKeys as $key){
		// 				$res[$key]= $key.': '.$res[$key];

		$selectData = array();
// 		$selectDataSolicitudes = array();
// 		$selectDataSolicitudEspecifica = array();

		foreach ($result as $res) {

			$selectData[$res['solicitud']] = $res;// implode('<br>',$res);
		}

		$solicitud_id = $selectData[$res['solicitud']]['solicitud'];


		if($this->getRequest()->isPost()) {
			$data = array_merge_recursive(
					$this->getRequest()->getPost()->toArray(),
					// Notice: make certain to merge the Files also to the post data
					$this->getRequest()->getFiles()->toArray()
			);

			$form->setData($data);
			if($form->isValid()) {

				$sql2 = new Sql($dbAdapter);


				if(isset($data['Pendiente'])) {
					$update = $sql2->update('solicitudes')
					->set(array(
							'estado_solicitud' => 'PEND',
					))
				  	->where(array('solicitud' => $solicitud_id));

				  $statement = $sql2->prepareStatementForSqlObject($update);
				  $results = $statement->execute();

				} else if (isset($data['Anular'])) {
					$update = $sql2->update('solicitudes')
					->set(array(
							'etapa_actual'	   => 'FINAL',
							'estado_solicitud' => 'ANUL',
					))
					->where(array('solicitud' => $solicitud_id));

					$statement = $sql2->prepareStatementForSqlObject($update);
					$results = $statement->execute();

				} else if (isset($data['Rechazar'])) {
					$update = $sql2->update('solicitudes')
					->set(array(
							'etapa_actual'	   => 'FINAL',
							'estado_solicitud' => 'RECHAZ',
					))
					->where(array('solicitud' => $solicitud_id));

					$statement = $sql2->prepareStatementForSqlObject($update);
					$results = $statement->execute();

				} else if (isset($data['VistoBueno'])) {
					$update = $sql2->update('solicitudes')
					->set(array(
							'etapa_actual'	   => 'DEL_DE',
							'estado_solicitud' => 'NUEVO',
					))
					->where(array('solicitud' => $solicitud_id));

					$statement = $sql2->prepareStatementForSqlObject($update);
					$results = $statement->execute();

				} else if (isset($data['EnviarCorreo'])) {

				} else if (isset($data['Imprimir'])) {

				}

				$this->flashmessenger()->addSuccessMessage('Operación realizada con éxito');

				// redirect the user to the view user action
				return $this->redirect()->toRoute('user/default', array (
						'controller' => 'account',
						'action'     => 'me',
				));
			}
		}


//		$resultmerged = array_merge($resultUsuarios, $resultSolicitudes, $resultSolicitudEspecifica);
		return array('data' => $selectData, 'form1'=> $form);
	}

	public function decanoAction()
	{
		$database = new DatabaseAdapter(); //instanciamos la clase cuyo metodo nos devuelve el adaptador de nuestra bd
		$dbAdapter = $database->createService($this->getServiceLocator()); //llamamos al metodo que nos devuelve el adaptador de bd

		$form = new DiSolicitudExtraordinarioForm($dbAdapter);


		$sql = 	'SELECT s.solicitud as solicitud, mesa_entrada, fecha_solicitada, nombres, apellidos, carrera, telefono, email,
			asignatura, fecha_extraordinario, motivo, archivo, cumple_fecha, inscripto_tercera_op, ausente_tercera_op
			FROM usuarios as u INNER JOIN solicitudes as s ON (u.usuario = s.usuario_solicitante)
			INNER JOIN solicitud_de_extraordinario as se ON (s.solicitud = se.solicitud)
			INNER JOIN asignaturas_por_solicitud as aso ON (s.solicitud = aso.solicitud)
			LEFT OUTER JOIN documentos_adjuntos as d ON (se.solicitud = d.solicitud)
			WHERE s.etapa_actual = \'DEL_DE\'
			ORDER BY mesa_entrada DESC
			LIMIT 1';
		// 		$sqlUsuarios = 'SELECT nombres, apellidos, telefono, email FROM usuarios ';
		// 		$sqlSolicitudEspecifica = 'SELECT fecha_extraordinario, motivo, cumple_fecha, ausente_tercera_op, inscripto_tercera_op FROM solicitud_de_extraordinario';

		$statement = $dbAdapter->query($sql);
		// 		$statementSolicitudEspecifica = $dbAdapter->query($sqlSolicitudEspecifica);
		// 		$statementUsuarios = $dbAdapter->query($sqlUsuarios);

		$result = $statement->execute();
		// 		$resultSolicitudEspecifica = $statementSolicitudEspecifica->execute();
		// 		$resultUsuarios  = $statementUsuarios->execute();

		// 			$arrayKeys = array_keys($res);
		// 			foreach ($arrayKeys as $key){
		// 				$res[$key]= $key.': '.$res[$key];

		$selectData = array();
		// 		$selectDataSolicitudes = array();
		// 		$selectDataSolicitudEspecifica = array();

		foreach ($result as $res) {

			$selectData[$res['solicitud']] = $res;// implode('<br>',$res);
		}

		$solicitud_id = $selectData[$res['solicitud']]['solicitud'];


		if($this->getRequest()->isPost()) {
			$data = array_merge_recursive(
					$this->getRequest()->getPost()->toArray(),
					// Notice: make certain to merge the Files also to the post data
					$this->getRequest()->getFiles()->toArray()
			);

			$form->setData($data);
			if($form->isValid()) {

				$sql2 = new Sql($dbAdapter);

				if(isset($data['Aprobar'])) {
					$update = $sql2->update('solicitudes')
					->set(array(
							'etapa_actual'	   => 'FINAL',
							'estado_solicitud' => 'APROB',
					))
					->where(array('solicitud' => $solicitud_id));

					$statement = $sql2->prepareStatementForSqlObject($update);
					$results = $statement->execute();

				} else if(isset($data['Pendiente'])) {
					$update = $sql2->update('solicitudes')
					->set(array(
							'estado_solicitud' => 'PEND',
					))
					->where(array('solicitud' => $solicitud_id));

					$statement = $sql2->prepareStatementForSqlObject($update);
					$results = $statement->execute();

				} else if (isset($data['Anular'])) {
					$update = $sql2->update('solicitudes')
					->set(array(
							'etapa_actual'	   => 'FINAL',
							'estado_solicitud' => 'ANUL',
					))
					->where(array('solicitud' => $solicitud_id));

					$statement = $sql2->prepareStatementForSqlObject($update);
					$results = $statement->execute();

				} else if (isset($data['Rechazar'])) {
					$update = $sql2->update('solicitudes')
					->set(array(
							'etapa_actual'	   => 'FINAL',
							'estado_solicitud' => 'RECHAZ',
					))
					->where(array('solicitud' => $solicitud_id));

					$statement = $sql2->prepareStatementForSqlObject($update);
					$results = $statement->execute();

				} else if (isset($data['EnviarCorreo'])) {

				} else if (isset($data['Imprimir'])) {

				}

				$this->flashmessenger()->addSuccessMessage('Operación realizada con éxito');

				// redirect the user to the view user action
				return $this->redirect()->toRoute('user/default', array (
						'controller' => 'account',
						'action'     => 'me',
				));
			}
		}

		//		$resultmerged = array_merge($resultUsuarios, $resultSolicitudes, $resultSolicitudEspecifica);
		return array('data' => $selectData, 'form1'=> $form);
	}

	public function resultadorequisitosAction()
	{
		$database = new DatabaseAdapter(); //instanciamos la clase cuyo metodo nos devuelve el adaptador de nuestra bd
		$dbAdapter = $database->createService($this->getServiceLocator()); //llamamos al metodo que nos devuelve el adaptador de bd


		$form = new ResultadoRequisitosForm($dbAdapter);

		$sql = 	'SELECT s.solicitud as solicitud, mesa_entrada, fecha_solicitada, resultado_requisitos, nombres, apellidos, carrera, telefono, email,
		asignatura, fecha_extraordinario, motivo, archivo, cumple_fecha, inscripto_tercera_op, ausente_tercera_op
		FROM usuarios as u INNER JOIN solicitudes as s ON (u.usuario = s.usuario_solicitante)
		INNER JOIN solicitud_de_extraordinario as se ON (s.solicitud = se.solicitud)
		INNER JOIN asignaturas_por_solicitud as aso ON (s.solicitud = aso.solicitud)
		LEFT OUTER  JOIN documentos_adjuntos as d ON (se.solicitud = d.solicitud)
		ORDER BY mesa_entrada DESC
		LIMIT 1';


		$statement = $dbAdapter->query($sql);

		$result = $statement->execute();


		$selectData = array();


		foreach ($result as $res) {

			$selectData[$res['solicitud']] = $res;
		}


		return array('data' => $selectData, 'form1'=> $form);
	}
}
