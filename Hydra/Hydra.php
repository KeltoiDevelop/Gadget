<?php
//INTERFACES
interface iEntity
{
	public function getId();
	public function setId($id);
	public function getValues();
}

interface iView
{
	public function toJson();
}

interface iObject extends iEntity
{
	public function getDescription();	
	public function setDescription($description);
}

interface iThing extends iEntity
{
	public function getName();	
	public function setName($name);
}

interface iLabel extends iEntity
{
	public function getCaption();
	public function setCaption($caption);
}

interface iDataFactory
{
	public function createInstance($id);
	public function createInstanceByDataRow($row);
}

//PROCEDURE 
class Procedure 
{
	private $name;
	private $parameters;
	
	public function __construct($name,$parameters)
	{
		$this->name = $name;
		$this->parameters = $parameters;
	}
	
	public function getName()
	{
		return $this->name;
	}
	
	public function getParameters()
	{
		return $this->parameters;
	}
}

//DATA PROVIDER
class ProviderFileConfig
{
	private $dbms;
	private $host;
	private $database;
	private $login;
	private $password;
	private $callVerb;
	private $mustCallVerb;
	
	public function __construct()
	{
		$obj = json_decode(file_get_contents("provider.json"));
		
		$this->dbms = $obj->dbms;
		$this->host = $obj->host;
		$this->database = $obj->database;
		$this->login = $obj->login;
		$this->password = $obj->password;
		$this->callVerb = $obj->callVerb;
		$this->mustCallVerb = $obj->mustCallVerb;
	}
	
	public function getDboString()
	{
		return $this->dbms . ":host=" . $this->host . ";dbname=" . $this->database;
	}
	
	public function getLogin()
	{
		return $this->login;
	}
	
	public function getPassword()
	{
		return $this->password;
	}
	
	public function getCall()
	{
		return ($this->mustCallVerb)?$this->callVerb. ' ':'';
	}
}

class Provider
{
	private $dsn;
	private $user;	
	private $password;
	private $call;
	
	public function __construct($dsn,$user,$password)
	{
		$this->dsn = $dsn;
		$this->user = $user;
		$this->password = $password;
	}
	
	public function getPdo()
	{
		return new PDO($this->dsn,$this->user,$this->password);
	}
	
	public function setCall($call)
	{
		$this->call = $call;
	}
	
	public function getCall()
	{
		return $this->call;
	}
	
	public static function ByConfig(ProviderFileConfig $fileConfig)
	{
		$pdo = new Provider($fileConfig->getDboString(),$fileConfig->getLogin(),$fileConfig->getPassword());
		$pdo->setCall($fileConfig->getCall());
		return $pdo;
	}
}

class EntityInfo
{
	private $name;
	private $id;
	
	public function __construct(iEntity $entity)
	{
		$this->name = get_class($entity);
		$this->id = $entity->getId();
	}
	
	public function getName()
	{
		return $this->name;
	}
	
	public function getId()
	{
		return $this->id;
	}
}

//HELPER
class EntityDataHelper
{
	private $entityName;
	private $entityId;
	private $entityValues;
	
	public function __construct(iEntity $entity)
	{
		$this->entityId = $entity->getId();
		$this->entityName = get_class($entity);
		$this->entityValues = implode($entity->getValues(),',');
	}

	public function getId()
	{
		return $this->entityId;
	}
	
	public function getName()
	{
		return $this->entityName;
	}
	
	public function getValues()
	{
		return $this->entityValues;
	}
}

class LinkerDataHelper
{
	private $oneEntityInfo;
	private $manyEntityInfo;
	
	public function __construct(EntityInfo $oneEntityInfo, EntityInfo $manyEntityInfo)
	{
		$this->oneEntityInfo = $oneEntityInfo;
		$this->manyEntityInfo = $manyEntityInfo;
	}
	
	public function getBindStatement()
	{
		$procName = $this->oneEntityInfo->getName() . $this->manyEntityInfo->getName() . 'Bind';
		return $procName . '(' . $this->oneEntityInfo->getId() . ',' . $this->manyEntityInfo->getId() . ')' ;
	}
	
	public function getListStatement()
	{
		return $this->manyEntityInfo.getName() . 'ListBy' . $this->oneEntityInfo.getName(). '('. $this->oneEntityInfo.getId() .')';
	}
	
	public function getPickStatement()
	{
		return $this->oneEntityInfo.getName() . 'PickBy'. $this->manyEntityInfo.getName() .'('. $this->manyEntityInfo.getId() .')';
	}
}

class CrudHelper
{
	private $dataHelper;
	
	public function __construct(EntityDataHelper $dataHelper)
	{
		$this->dataHelper = $dataHelper;
	}	

	public function getCreateStatement()
	{
		return $this->dataHelper->getName() . 'Insert(' . $this->dataHelper->getValues() . ')';
	}
	
	public function getReadStatement()
	{
		return $this->dataHelper->getName() . 'Get(' . $this->dataHelper->getId(). ')';
	}
	
	public function getUpdateStatement()
	{
		return $this->dataHelper->getName() . 'Update(' . $this->dataHelper->getValues() . ',' . $this->dataHelper->getId() . ')';
	}
	
	public function getDeleteStatement()
	{
		return $this->dataHelper->getName() . 'Delete(' . $this->dataHelper->getId() . ')';
	}
}

class ProcedureHelper
{
	private $procedure;

	public function __construct(Procedure $procedure)
	{
		$this->procedure = $procedure;
	}
		
	public function getProcedureStatement()
	{
		$p = implode(',',$this->procedure->getParameters());
		return $this->procedure->getName() . '(' . $p . ')';
	}
}

//DATA OBJECTS
abstract class DataOperation
{
	protected $provider;

	protected function __construct(Provider $provider)
	{
		$this->provider = $provider;
	}
	
	protected function executeObjectId($statement)
	{
		$pdo = $this->provider->getPdo();
		$sth = $pdo->prepare($this->provider->getCall() . $statement);
		$sth->execute();
		$objId = $sth->fetchColumn();
		$sth->closeCursor();
		$sth = null;
		$pdo = null;	
		
		return $objId;
	}
	
	protected function executeDataObject($statement,$factory)
	{
		$pdo = $this->provider->getPdo();
		$sth = $pdo->prepare($this->provider->getCall() . $statement);
		$sth->execute();
		$row = $sth->fetch(PDO::FETCH_OBJ);
		$result = $factory->createInstanceByDataRow($row);
		$sth->closeCursor();
		$sth = null;
		$pdo = null;
		
		return $result;
	}
	
	protected function execute($statement)
	{
		$pdo = $this->provider->getPdo();
		$sth = $pdo->prepare($this->provider->getCall() . $statement);
		$sth->execute();
		$sth->closeCursor();
		$sth = null;
		$pdo = null;		
	}
	
	protected function listing($statement,$factory)
	{
		$array = array();
		$pdo = $this->provider->getPdo();
		$sth = $pdo->prepare($this->provider->getCall() . $statement);
		$sth->execute();
		while($row = $sth->fetch(PDO::FETCH_OBJ))
		{
			array_push($array,$factory->createInstanceByDataRow($row));
		}
		
		$sth->closeCursor();
		$sth = null;
		$pdo = null;
		
		return $array;
	}
}

class Crud extends DataOperation
{	
	private $helper;
	
	public function __construct(Provider $provider, CrudHelper $helper)
	{
		parent::__construct($provider);
		$this->helper = $helper;
	}

	public function create()
	{
		$objId = $this->executeObjectId($this->helper->getCreateStatement());	
		
		return $objId;
	}
	
	public function read(iDataFactory $factory)
	{
		$obj = $this->executeDataObject($this->helper->getReadStatement(),$factory);
		return $obj;
	}
		
	public function update()
	{
		$this->execute($this->helper->getUpdateStatement());
	}
	
	public function deleting()
	{
		$this->execute($this->helper->getDeleteStatement());
	}
}

class Linker extends DataOperation
{
	private $helper;
	
	public function __construct(Provider $provider,LinkerDataHelper $helper)
	{
		parent::__construct($provider);
		$this->helper = $helper;
	}
	
	public function bind()
	{
		$this->execute($this->helper->getBindStatement());
	}
	
	public function listBy(iDataFactory $factory)
	{
		$array = $this->listing($this->helper->getListStatement(),$factory);
		return $array;
	}
	
	public function pickBy(iDataFactory $factory)
	{
		$obj = $this->executeDataObject($this->helper->getPickStatement(),$factory);
		return $obj;
	}
	
}

class Lister extends DataOperation
{
	private $helper;
	
	public function __construct(Provider $provider, ProcedureHelper $helper)
	{
		parent::__construct($provider);
		$this->helper = $helper;
	}
	
	public function executeData(iDataFactory $factory)
	{	
		$array = $this->listing($this->helper->getProcedureStatement(),$factory);
		return $array;
	}
	
	public function execute()
	{
		$array = array();
		$pdo = $this->provider->getPdo();
		$sth = $pdo->prepare($this->helper->getProcedureStatement());
		$sth->execute();
		while($row = $sth->fetch(PDO::FETCH_OBJ))
		{
			array_push($array,$row);
		}
		
		$sth->closeCursor();
		$sth = null;
		$pdo = null;
		
		return $array;		
	}
}

class Paging
{
	private $elementsByPage;
	private $page;
	private $maxPage;
	private $elements;
	
	public function __construct ($elementsByPage)
	{
		$this->elementsByPage = $elementsByPage;
		$this->page = 1;
	}
	
	public function setElements(iDataFactory $dataFactory, Lister $listing)
	{
		$this->elements = $listing->executeData($dataFactory);
		$this->maxPage = ceil(count($this->elements)/$this->elementsByPage);
	}
	
	public function getElements()
	{
		return array_slice($this->elements,(($this->page-1)*$this->elementsByPage),$this->elementsByPage);
	}
	
	public function getMaxPage()
	{
		return $this->maxPage;
	}
	
	public function getElementsByPage()
	{
		return $this->elementsByPage;
	}
	
	public function getPage()
	{
		return $this->page;
	}
	
	public function setPage($page)
	{
		$this->page = $page;
	}
	
	public function next()
	{
		$this->page += ($this->page==$this->maxPage)?0:1;
	}
	
	public function previous()
	{
		$this->page-=($this->page==1)?0:1;
	}
	
	public function first()
	{
		$this->page = 1;
	}
	
	public function last()
	{
		$this->page = $this->maxPage;
	}
}

//Application implements
class AppProvider
{
	private $provider;
	
	public function __construct()
	{
		$pfc = new ProviderFileConfig();
		$this->provider = Provider::ByConfig($pfc);
	}
	
	public function getProvider()
	{
		return $this->provider;
	}
	
	public function __destruct()
	{
		$provider = NULL;
	}
}

class AppCrud
{
	private $crud;
	
	public function __construct(iEntity $entity,Provider $provider)
	{
		$helper = new EntityDataHelper($entity);
		$crudHelper = new CrudHelper($helper);
		$this->crud = new Crud($provider,$crudHelper);
	}
	
	public function getCrud()
	{
		return $this->crud;
	}
	
	public function __destruct()
	{
		$crud = NULL;
	}
}


class AppLinker
{
	private $linker;
	
	public function __construct(iEntity $one, iEntity $many, Provider $provider)
	{
		$oneInfo = new EntityInfo($one);
		$manyInfo = new ManyInfo($info);
		$helper = new LinkerDataHelper($oneInfo,$manyInfo);
		$this->linker = new Linker($provider,$helper);
	}
	
	public function getLinker()
	{
		return $this->linker;
	}
	
	public function __destruct()
	{
		$linker = NULL;
	}
}

class AppLister
{
	private $lister;
	
	public function __construct(Procedure $procedure,Provider $provider)
	{
		$helper = new ProcedureHelper($procedure);
		$this->lister = new Lister($provider,$helper);
	}
	
	public function getLister()
	{
		return $this->lister;
	}
	
	public function __destruct()
	{
		$this->lister=NULL;
	}
}

//Repository
class Repository
{
	private $entity;
	private $provider;
	private $dataFactory;
	
	public function __construct(Provider $provider,iDataFactory $dataFactory)
	{
		$this->provider = $provider;
		$this->dataFactory = $dataFactory;
	}
	
	public function __destruct()
	{
		$this->provider = NULL;
		$this->dataFactory = NULL;	
	}
	
	public function get()
	{
		return $this->entity;
	}
	
	public function set(iEntity $entity)
	{
		$this->entity = $entity;
	}
	
	public function delete($id)
	{
		$this->entity = $this->dataFactory->createInstance($id);
		$crud = new AppCrud($this->entity,$this->provider);
		$crud->getCrud()->deleting();
		$crud=NULL;	
	}
	
	public function read($id)
	{
		$this->entity = $this->dataFactory->createInstance($id);
		$crud = new AppCrud($this->entity,$this->provider);
		$this->entity = $crud->getCrud()->read($this->dataFactory);
		$crud=NULL;
	}
	
	public function save()
	{	
		$crud = new AppCrud($this->entity,$this->provider);
		$crudInstance = $crud->getCrud();
		if ($this->entity->getId()==NULL)
		{
			$id = $crudInstance->create();
			$this->entity->setId($id);
		}
		else
		{
			$crudInstance->update();
		}
		$crud = NULL;
		$crudInstance=NULL;
	}
}

?>