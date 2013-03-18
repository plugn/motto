<?php

/** ================================ DBTree =============================================
 *
 *  ( implements Modified Preordered Tree Traversal with no parent_id column usage )
 *
 *
 *  getInfo( $item_id, $field = null )
 *      сканирует связанную с деревом информационную таблицу и возвращает "паспорт" элемента:
 *          если поле указано, возвращает значение поля строкой,
 *          иначе всю запись массивом
 *
 *  getNodeLR( $id ) //
 *      Node - это один элемент, без потомков.
 *      возвращает array('lft'=>'', 'rgt' => '') или  false
 *
 *  getNodeId_LR(  $lft = null, $rgt = null )
 *      возвращает "id" элемента по индексу и проверяет его положение [left|right]
 *      возвращает false, если положение задано неверно
 *
 *      применение метода оправдано для проверки целостности структуры дерева
 *
 *      getNodeId_LR( null, 18 ) // по правому индексу
 *      getNodeId_LR( 2 )        // по левому
 *
 *  getNodeId( $index );
 *      возвращает "id" элемента по левому_или_правому_индексу без указания его положения
 *      getNodeId( 18 )
 *
 *  display( $root )
 *      управляет отображением [под-]дерева, для которого $root  - id корня
 *      для отображения каждого элемента вызывает displayOne()
 *
 *  displayOne ( $item_id, $indent )
 *      Oh yeah! Override It to customize View!
 *
 *  getPathToNode ( $id )
 *      рисуем маршрут от корня к элементу,
 *      вот интересно, потребуется ли параметр корневого элемента? пока необходимость неочевидна
 *
 *  renderPathToNode( $node_id, $glue, $use_info_field )
 *      $path = $db_tree->renderPathToNode ( $id, "/", "name" );
 *
 *  dropNode( $id )
 *      удаляет узел (без потомков)
 *
 *  addNode ( $parent_id, $info_hash )
 *     добавляет узел в структуру, записывает информацию в параллельную инфо-таблицу
 *     $db_tree->addNode($new_parent_id, array("name"=>"sparrowberry") );
 *
 *  moveSubTree( $id, $target_id )  /**
 *     SubTree - поддерево, $id  - <id> корневого элемента поддерева.
 *     $target_id - <id> будущего родителя движимого поддерева.
 *     После выполнения moveSubTree() корневой элемент поддерева станет последним
 *     и самым правым элементом среди собратьев ( siblings )
 *
 *  rebuild($parent, $left)
 *     перестраивает <lft> и <rgt> индексы, считывая данные родительского столбца parent_id.
 *     в этом классе <parent_id> не используется и не синхронизируется с состоянием структуры,
 *     тем не менее метод может оказаться полезен для сброса структуры в исходное состояние
 *
 *  ==================================== max@ v 0.3 ==========================================
 *  для примера очень понадобится rocket/core/classes
 *       DBAL.php + DBAL_mysql.php c моей правкой в методе Query() !!!
 */

class DBTree
{
  var $db_info;
  var $db_tree;

  function DBTree( &$db, $tree_table, $info_table, $cfg )
  {
    $this->db =& $db;
    $this->db_tree = $this->db->prefix.$tree_table;
    $this->db_info = $this->db->prefix.$info_table;
    $this->cfg = $cfg;
    $this->tbl = &new DbUtil($db, $this->db->prefix.$info_table);
  }

  function getInfo( $item_id, $field = null )
  {
    $a = $this->db->sql2array( "select * from ".$this->db_info.
                            " where id = ".$this->db->quote( $item_id ).";" );
    if ( !empty($a) )    // (!) Query() returns array guaranteed
      if ($field === null)  return $a;
      else  return $a[0][$field];
    return false;
  }

  function setInfo( $item_id, $data=null )
  {
    if ( $item_id && !empty($data)) {
        $this->tbl->Cleanup( $id_aff=true );
        $this->tbl->SetData( array_merge( $data, array('id'=>$item_id)), $id_aff=true );
        $this->tbl->Save( $mode='update' );
    }
  }

  function getNodeId_LR( $lft = null, $rgt = null )
  {
    if      (isset($lft)) $sql = "select * from ".$this->db_tree." where lft = ".$this->db->quote($lft);
    elseif  (isset($rgt)) $sql = "select * from ".$this->db_tree." where rgt = ".$this->db->quote($rgt);

    // $result = $this->db->QueryOne($sql); // (!) array
    $result = $this->db->sql2array($sql." limit 0,1"); // (!) array
    if ( !empty($result) ) return $result["id"];
    return $result;
  }

  function getNodeId( $idx )
  {
    $sql = "select * from ".$this->db_tree." where lft = ".$this->db->quote($idx)." or rgt = ".$this->db->quote($idx);

    $result = $this->db->sql2array($sql); // (!) array, !!! NOT RecordSet !!!
    if ( !empty($result) ) return $result["id"];
    return $result;
  }

  function getNodeLR( $id ) // (!) array or false
  {
    $sql = "select lft, rgt from ".$this->db_tree." where id = ".$this->db->quote($id)." limit 0,1";
    $result = $this->db->sql2array($sql); // (!) array, !!! NOT RecordSet !!!
    if (!empty($result) ) return $result[0];
    else        return false;
  }

  // Main building 'Controller'
  function display( $root )
  {
    // retrieve the left and right value of the $root node
    $qr = 'select lft, rgt from '.$this->db_tree.
                                 ' where id='.$this->db->quote( $root );
    $result = $this->db->query($qr); // echo $qr.' ^&^ ';  var_export( $result );
    $row = $this->db->fetch_assoc($result);

    // start with an empty <right> stack
    $right = array();
    // now, retrieve all descendants of the <root> node
    $result = $this->db->query('select id, lft, rgt from '.$this->db_tree.
                         ' where lft between '.$row['lft'].' and '. $row['rgt'].' order by lft asc;');
    // display each row
    while ($row = $this->db->fetch_assoc($result))  { // only check stack if there is one
      if (count($right)>0) {  // check if we should remove a node from the stack
         while ($right[count($right)-1]<$row['rgt']) {
             array_pop($right);
         }
      }
      // display indented node title
      $r .= $this->displayOne($row['id'], count($right));

      // add this node to the stack
      $right[] = $row['rgt'];
    }

    return $r;

  }

  // 'View'
  // following method is recommended to override in descendant classes for View handling
  function displayOne( $item_id, $indent )
  {
    $item_LR = $this->getNodeLR( $item_id );

    echo str_repeat('  ', $indent).$this->getInfo($item_id, 'name' ).
    ' [#'.$item_id.": ".
          $item_LR['lft'].','.
          $item_LR['rgt'].
     "] \n";
  }

  function getPathToNode( $node_id )
  {
    $r = $this->getNodeLR( $node_id ); // echo '$this->getNodeLR( '.$node_id.' ) :'; var_export($r);
    if ( !empty($r) )
    {
      $sql = "select id from ".$this->db_tree." where lft < ".$this->db->quote($r["lft"]).
             " and rgt > ".$this->db->quote($r["rgt"])." order by lft asc" ;
      $result = $this->db->sql2array( $sql );
      return $result;
    }
    return false;
  }

  function renderPathToNode( $node_id, $glue, $use_info_field )
  {
    $a = $this->getPathToNode( $node_id ); // echo 'this->getPathToNode( '.$node_id.' ) <br />' ;   var_export( $a );
    foreach ($a as $v)
      $path[]=$this->getInfo($v["id"], $use_info_field );
    $result = implode($glue, $path );

    return $result;
  }

  function dropNode( $id )
  {
    $idxs = $this->getNodeLR( $id );  // array or false
    if (!is_array($idxs)) return trigger_error('dropNode ('.$id.') : non-exististent node ID ');

    $lft = $idxs['lft'];
    $rgt = $idxs['rgt'];
    // manage structure
    $sqls[] = "update ".$this->db_tree." set rgt = rgt - 2 where rgt > ".$this->db->quote($rgt);
    $sqls[] = "update ".$this->db_tree." set lft = lft - 2 where lft > ".$this->db->quote($rgt);
    // normalize descendants
    $sqls[] = "update ".$this->db_tree." set lft = lft - 1, rgt = rgt - 1 ".
              "where lft > ".$this->db->quote($lft)." and rgt < ".$this->db->quote($rgt);
    $sqls[] = "delete from ".$this->db_tree." where id = ".$this->db->quote($id);
    $sqls[] = "delete from ".$this->db_info." where id = ".$this->db->quote($id);

    foreach ($sqls as $sql)
      if ( !$this->db->query($sql) ) // апдэйты и прочие non-select queries
        return trigger_error('mysql error @ '.__FILE__.':'.__LINE__ .' ! '.$sql . "\n ");

    // clean info
      $sql  = "delete from ".$this->db_info." where id = ".$this->db->quote($id);
      if (!$this->db->query($sql))
        return trigger_error('mysql error @ '.__FILE__.':'.__LINE__ .' !'.$sql . "\n ");
  }

  function addNode ( $parent_id, $info_hash )
  {
    $COL = ""; $VAL = "";

    $parent_idxs = $this->getNodeLR( $parent_id );
    $rgt = $parent_idxs['rgt'];

    // set structure
    $sqls[] = "update ".$this->db_tree." set rgt = rgt + 2 where rgt > ".$this->db->quote($rgt-1);
    $sqls[] = "update ".$this->db_tree." set lft = lft + 2 where lft > ".$this->db->quote($rgt-1);

    $sqls[] = "insert into ".$this->db_tree." set lft=".$this->db->quote($rgt).", rgt=".$this->db->quote($rgt+1);
    foreach ($sqls as $sql)
      if ( !$this->db->query($sql) ) // апдэйты и прочие non-select queries
        return trigger_error('mysql error @ '.__FILE__.':'.__LINE__ .' !'.$sql . "\n ");

    // set info
    if (is_array($info_hash) && !empty($info_hash))
    {
      $info_hash['id']=$this->db->insert_id();
      foreach ($info_hash as $field => $value )
      { $COL .=  " `".$field."`,"; $VAL .= " '".$value."',"; }
            $COL = substr($COL, 0, strlen($COL) - 1);
      $VAL = substr($VAL, 0, strlen($VAL) - 1);

      $sql  = "insert into ".$this->db_info." ( ".$COL." ) values ( ".$VAL." )";
      if (!$this->db->query($sql))
        return trigger_error('mysql error @ '.__FILE__.':'.__LINE__ .' !'.$sql . "\n ");
    }
  }

  function moveSubTree( $id, $target_id )
  {
    // 0a. subtree root node recognition.  retrieving "lft" and "rgt" keys
    if ( !$subtree = $this->getNodeLR( $id ))        return trigger_error('moveSubTree() invalid id ');
    // 0b. target parent node recognition. retrieving "lft" and "rgt" keys
    if ( !$target = $this->getNodeLR( $target_id ))  return trigger_error('moveSubTree() invalid target_id ');

    // 1. movement direction detection
    if     ( $subtree['lft'] < $target['lft'] && $subtree['rgt'] > $target['rgt'] )
    {
      echo BR.('moveSubTree() : can\'t move subtree inside itself').BR;  return;
      // return trigger_error('moveSubTree() : can\'t move subtree inside itself');
    }

    if     ( $subtree['lft'] > $target['lft'] && $subtree['rgt'] > $target['rgt'] )  $direction = 'R2L';
    elseif ( $subtree['rgt'] < $target['rgt'] )  $direction = 'L2R';
    else  return trigger_error('moveSubTree() : invalid node structure ');

    // 2. go ahead
    switch ( $direction ) {
    case "R2L":           // I

      $edx_qty = $subtree['rgt'] - $subtree['lft'] + 1;
      $delta =   $target['rgt'] - $subtree['lft'];

      // запоминаем ids поддерева, потому что lft и rgt смешиваются в следующем шаге
      $sql = "select id from ".$this->db_tree." where rgt <= '".$subtree['rgt']."'".
                                              "   and lft >= '".$subtree['lft']."'";
      $a = $this->db->sql2array( $sql );
      foreach ($a as $v) $subtree_ids[] = $v['id'];      // var_export($subtree_ids);

      // освобождаем диапазон индексов
      $sqls[] = "update ".$this->db_tree." set rgt = rgt + ".$edx_qty.
                " where rgt >= '".$target['rgt']."'".
                "   and rgt <  '".$subtree['lft']."'";

      $sqls[] = "update ".$this->db_tree." set lft = lft + ".$edx_qty.
                " where lft > '".$target['rgt']. "'".
                "   and lft < '".$subtree['lft']."'";

      // пересчитываем поддерево
      $sqls[] = 'update '.$this->db_tree.' set rgt=rgt+('.$delta.'), lft=lft+('.$delta.') '.
                'where id in ('.implode(', ', $subtree_ids).') ';

      //echo BR; var_export($sqls);      die();
      foreach ($sqls as $sql) // echo $sql. BR;
        if ( !$this->db->query($sql) ) // апдэйты и прочие non-select queries
          return trigger_error('mysql error @ '.__FILE__.':'.__LINE__ .' !'.$sql . "\n ");

      break;

    case "L2R":           // II

      $edx_qty = $subtree['rgt'] - $subtree['lft'] + 1;
      $delta =   $target['rgt'] - $subtree['rgt'] - 1;

      // запоминаем ids поддерева, потому что lft и rgt смешиваются в следующем шаге
      $sql = "select id from ".$this->db_tree." where rgt <= '".$subtree['rgt']."'".
                                              "   and lft >= '".$subtree['lft']."'";
      $a = $this->db->sql2array( $sql );
      foreach ($a as $v) $subtree_ids[] = $v['id'];      // var_export($subtree_ids);

      // освобождаем диапазон индексов
      $sqls[] = "update ".$this->db_tree." set rgt = rgt - ".$edx_qty.
                " where rgt > '".$subtree['rgt']."'".
                "   and rgt < '". $target['rgt']."'";

      $sqls[] = "update ".$this->db_tree." set lft = lft - ".$edx_qty.
                " where lft > '".$subtree['rgt']."'".
                "   and lft < '". $target['rgt']."'";

      // пересчитываем поддерево
      $sqls[] = 'update '.$this->db_tree.' set rgt=rgt+('.$delta.'), lft=lft+('.$delta.') '.
                'where id in ('.implode(', ', $subtree_ids).') ';

      foreach ($sqls as $sql)
        if ( !$this->db->query($sql) ) // апдэйты и прочие non-select queries
          return trigger_error('mysql error @ '.__FILE__.':'.__LINE__ .' ! '.$sql . "\n ");

      break;
    }

  }

/**
 * поскольку мы можем вовсе не хранить родительский столбец,
 * этот метод вообще останется без использования
 * как только мы научимся добавлять и правильно удалять ( инверсивно добавлению )  узлы дерева
 */

  function rebuild($parent, $left)
  {
    // the right value of this node is the left value + 1
    $right = $left+1;
    // get all children of this node
    $sql = 'select id from '.$this->db_tree.' where parent_id='.$this->db->quote($parent);
    // $result = mysql_query(
    $result = $this->db->query($sql);

    // recursive execution of this function for each child of this node
    // $right is the current right value, which is incremented by the rebuild_tree function
    while ($row = $this->db->fetch_assoc($result))
      $right = $this->rebuild($row['id'], $right);

      // we've got the left value, and now that we've processed
      // the children of this node we also know the right value
    $sql  = "update ".$this->db_tree." set lft=".$left.", rgt=".$right." where id=".$this->db->quote($parent);
    $result = $this->db->query($sql);
    // return the right value of this node + 1
    return $right+1;
  }

} // EOC { DBTree }

?>