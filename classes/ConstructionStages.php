<?php

class ConstructionStages
{
	private $db;

	public function __construct()
	{
		$this->db = Api::getDb();
	}

	public function getAll()
	{
		$stmt = $this->db->prepare("
			SELECT
				ID as id,
				name, 
				strftime('%Y-%m-%dT%H:%M:%SZ', start_date) as startDate,
				strftime('%Y-%m-%dT%H:%M:%SZ', end_date) as endDate,
				duration,
				durationUnit,
				color,
				externalId,
				status
			FROM construction_stages
		");
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function getSingle($id)
	{
		$stmt = $this->db->prepare("
			SELECT
				ID as id,
				name, 
				strftime('%Y-%m-%dT%H:%M:%SZ', start_date) as startDate,
				strftime('%Y-%m-%dT%H:%M:%SZ', end_date) as endDate,
				duration,
				durationUnit,
				color,
				externalId,
				status
			FROM construction_stages
			WHERE ID = :id
		");
		$stmt->execute(['id' => $id]);
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function post(ConstructionStagesCreate $data)
	{
		$stmt = $this->db->prepare("
			INSERT INTO construction_stages
			    (name, start_date, end_date, duration, durationUnit, color, externalId, status)
			    VALUES (:name, :start_date, :end_date, :duration, :durationUnit, :color, :externalId, :status)
			");
		$stmt->execute([
			'name' => $data->name,
			'start_date' => $data->startDate,
			'end_date' => $data->endDate,
			'duration' => $data->duration,
			'durationUnit' => $data->durationUnit,
			'color' => $data->color,
			'externalId' => $data->externalId,
			'status' => $data->status,
		]);
		return $this->getSingle($this->db->lastInsertId());
	}
	
	/**
 * Updates the given Parameters by ID - TASK 1
 *
 * @param int     $id       the id of the row in Database
 * @param ConstructionStagesCreate      $data     the datacontext
 *
 * @return status
 */
	public function patchData(ConstructionStagesCreate $data, $id){
	
		// initialize local variables
		$name = "";
		$start_date ="";
		$end_date = "";
		$duration = "";
		$durationUnit = "";
		$color = "";
		$externalId = "";
		$status = "";
		
		
		// status Validation Check
		if($status == "NEW" || $status == "PLANNED" || $status == "DELETED"){
			
			// SELECT FROM DB TO GET CURRENT DATA
			$stmt = $this->db->prepare("
			SELECT
				ID as id,
				name, 
				strftime('%Y-%m-%dT%H:%M:%SZ', start_date) as startDate,
				strftime('%Y-%m-%dT%H:%M:%SZ', end_date) as endDate,
				duration,
				durationUnit,
				color,
				externalId,
				status
			FROM construction_stages
			WHERE ID = :id
		");
		$stmt->execute(['id' => $id]);
		
		// Give the local Variables the Information from the Database
		while($row = $stmt->fetchAll(PDO::FETCH_ASSOC)){
			$name = $row['name'];
			$start_date = $row['startDate'];
			$end_date = $row['endDate'];
			$duration = $row['duration'];
			$durationUnit = $row['durationUnit'];;
			$color = $row['color'];
			$externalId = $row['externalId'];
			$status = $row['status'];
			
		}
		
		// Overwrite  Variables with data that has been given from the POST Statement (API Call)
		$name = $data->name;
		$start_date = $data->startDate;
		$end_date = $data->endDate;
		$duration = $data->duration;
		$durationUnit = $data->durationUnit;
		$color = $data->color;
		$externalId = $data->externalId;
		$status = $data->status;
			
			// Update Statement
			$stmt1 = $this->db->prepare("
					UPDATE construction_stages 
					SET name = :name,
					start_date = :start_date,
					end_date = :end_date,
					duration = :duration,
					durationUnit = :durationUnit,
					color = :color,
					externalId = :externalId,
					status = :status
					WHERE id = :id
					");
				
				
				return $stmt1->execute([
					'name' => $name,
					'start_date' => $startDate,
					'end_date' => $endDate,
					'duration' => $duration,
					'durationUnit' => $durationUnit,
					'color' => $color,
					'externalId' => $externalId,
					'status' => $status,
				]);
	
		}else{
			return "error: status field is not NEW, PLANNED or DELETED";
		}
	
	}
	
		/**
 * Sets the Status to DELETED by ID - TASK 2
 *
 * @param int     $id       the id of the data row
 *
 * @return status of the sql statement
 */
	public function deleteStatement($id){
		$stmt = $this->db->prepare("
					status = 'DELETED'
					WHERE id = '".$id."'
					");
				
				
				return $stmt->execute();
		
	}
	
}