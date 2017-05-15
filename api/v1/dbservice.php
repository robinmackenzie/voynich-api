<?php

class dbService {

  const DSN = 'sqlite:vms.sqlite';
  const VMS_FACT = 'tokens';
  const TABLE_INFO_SQL = 'PRAGMA table_info('.self::VMS_FACT.')';
  const EVA_SINGLE_CHAR_PATTERN = 'a|b|c|d|e|f|g|h|i|j|k|l|m|n|o|p|q|r|s|t|u|v|x|y|z|\\*|,|!|%';
  const MORPHEME_DELIMITER = '_';
  
  public $db;
  public $meta;
  public $columnInfo;
    
  public function __construct() {
    // db initialisation plus set error level
    $this->db = new PDO(self::DSN);
    $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // create metadata
    $this->getMetadata();
  }
     
  public function queryAndMorphemizeData($filters, $columns, $sortedMorphemeList, $distinct) {
    // make regex from morpheme list
    $pattern = '('.implode('|', $sortedMorphemeList).'|'.self::EVA_SINGLE_CHAR_PATTERN.')';
    
    // get sql and predicate bindings
    $initialisedQuery = $this->initialiseQuery($filters, $columns, $distinct);
    
    // execute query
    $query = $this->db->prepare($initialisedQuery['sql']);
    $query->execute($initialisedQuery['bindings']);
    
    // get data
    $rows = $query->fetchAll($initialisedQuery['fetchStyle']); 

    // iterate data by reference and morphemize tokens 
    foreach($rows as &$row) {
      // get token if item field is in query
      if($initialisedQuery['fetchStyle'] == PDO::FETCH_COLUMN) {
        $token = $row;  
      } else if (isset($row['item'])) {
        $token = $row['item'];        
      } else {
        break;
      }
      
      // skip tokens beginning with { as they are comments
      if (substr($token, 0, 0) !== '}') {
        // run regex
        preg_match_all($pattern, $token, $morphemes, PREG_SET_ORDER);
        
        // create morphemized token as (morpheme1)(morpheme2)...(morphemeN)
        //$morphemizedToken = '';
        //foreach($morphemes as $morpheme) {
        //  $morphemizedToken .= '('.$morpheme[0].')';
        //}
        
        // create morphemized token as morpheme1_morpheme2...morphemeX
        $morphemizedTokenArray = array();
        foreach($morphemes as $morpheme) {
          $morphemizedTokenArray[] = $morpheme[0];
        }
        $morphemizedToken = implode(self::MORPHEME_DELIMITER, $morphemizedTokenArray);
        
        // update data
        if($initialisedQuery['fetchStyle'] == PDO::FETCH_COLUMN) {
          $row = $morphemizedToken;
        } else {
          $row['item'] = $morphemizedToken;
        }
      }
    }
    
    // return result
    return $rows;
  
  }
  
  public function queryData($filters, $columns, $distinct) {
    
    // get sql and predicate bindings
    $initialisedQuery = $this->initialiseQuery($filters, $columns, $distinct);
    
    // execute query
    $query = $this->db->prepare($initialisedQuery['sql']);
    $query->execute($initialisedQuery['bindings']);
        
    // get data
    $rows = $query->fetchAll($initialisedQuery['fetchStyle']); 
    
    return $rows;
  }
  
  public function queryMeta($columnName) {
    // base query
    $sql = 'select distinct '.$columnName.' from '.self::VMS_FACT;
    $fetchStyle = PDO::FETCH_COLUMN;
    
    // prepare query and apply parameter bindings
    $query = $this->db->prepare($sql);
    $query->execute();
    
    // add tokens to result
    return $query->fetchAll($fetchStyle);
  }
  
  private function getMetadata() {
    // get table metadata
    $query = $this->db->prepare(self::TABLE_INFO_SQL);
    $query->execute();
    $this->meta = $query->fetchAll(PDO::FETCH_ASSOC);
    
    // get array of columns from metadata
    $this->columnInfo = array_reduce($this->meta, function($result, $item) {
      $result[$item['name']] = $item['type']; 
      return $result;
    }, array());

  }
 
  private function initialiseQuery($filters, $columns, $distinct) {
    
    $result = array();
    
    // base query
    if(sizeof($columns) > 0) {
      // select the selected columns
      $sql = 'select '.implode(', ', $columns).' from '.self::VMS_FACT;
      $fetchStyle = PDO::FETCH_ASSOC;
    } else {
      // just select the tokens (check distinct flag)
      if ($distinct) {
        $sql = 'select distinct item from '.self::VMS_FACT;
      } else {
        $sql = 'select item from '.self::VMS_FACT;
      }
      $fetchStyle = PDO::FETCH_COLUMN;
    }
    
    // prepare arrays for predicates and predicate bindings
    $predicates = array();
    $bindings = array();
    
    // parse query filters from input uri
    foreach($filters as $key => $value) {
      // only process query parameter where key is in table columns
      if (in_array($key, array_keys($this->columnInfo))) {
        // get column data type
        $columnType = $this->columnInfo[$key];
        
        // if text or numeric then check for ,-delimited items
        // if tinyint then validate boolean-type flag
        switch ($columnType) {
          case 'text':
          case 'numeric':
            // test for comma delimited list
            if (strpos($value, ',') > -1 ) {
              // get 2 arrays from $items separating items
              // beginning with - with those not beginning with -
              $items = explode(',', $value);
              $inItems = array_values(array_filter($items, function($item) {
                return substr($item, 0, 1) !== '-';} 
              ));
              $notInItems = array_values(array_filter($items, function($item) {
                return substr($item, 0, 1) == '-';} 
              ));

              if (sizeof($inItems) > 1) {
                // create quoted list for predicate
                $clause = '';
                // add in items to predicates
                foreach($inItems as $index => $item) {
                  $clause .= ':'.$key.$index.',';
                  $bindings[':'.$key.$index] = $item;
                }
                // need to drop final comma with rtrim
                $predicates[] = $key.' in ('.rtrim($clause, ',').')';
              }
              
              if (sizeof($notInItems) > 1) {
                // create quoted list for predicate
                $clause = '';
                // add not-in items to predicates dropping - from value if strlen > 1
                foreach($notInItems as $index => $item) {
                  if (strlen($item) > 1) {
                    $item2 = substr($item, 1, strlen($item) - 1);
                    $clause .= ':'.$key.$index.',';
                    $bindings[':'.$key.$index] = $item2;
                  }
                }
                // need to drop final comma with rtrim
                $predicates[] = $key.' not in ('.rtrim($clause, ',').')';
              }
              
            } else {
              // create predicate based on equality or non-equality
              if (substr($value, 0, 1) == '-') {
                if (strlen($value) > 1) {
                  $value2 = substr($value, 1, strlen($value) - 1);
                  $predicates[] = $key.'<>:'.$key;
                  $bindings[':'.$key] = $value2;
                }
              } else {
                $predicates[] = $key.'=:'.$key;
                $bindings[':'.$key] = $value;
              }
            }
            break;
          case 'tinyint':
            // check for 0/1
            if ($value == '0' || $value == '1') {
              $predicates[] = $key.'=:'.$key;
              $bindings[':'.$key] = intval($value);
            }
            break;
        }       
      }
    }
      
    // add predicates to query
    if(sizeof($predicates) > 0) {
      $sql .= ' where ';
      $sql .= implode(' and ', $predicates);
    }    
    
    $result['sql'] = $sql;
    $result['fetchStyle'] = $fetchStyle;
    $result['bindings'] = $bindings;
    
    return $result;
    
  }
  
}





