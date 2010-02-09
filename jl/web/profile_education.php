<?php

require_once '../conf/general';
require_once '../phplib/page.php';
require_once '../phplib/journo.php';
require_once '../phplib/editprofilepage.php';
require_once '../phplib/eventlog.php';
require_once '../../phplib/db.php';
require_once '../../phplib/utility.php';



class EducationPage extends EditProfilePage
{

    function __construct() {
        $this->pageName = "education";
        $this->pageTitle = "Education";
        $this->pagePath = "/profile_education";
        $this->pageParams = array( 'head_extra_fn'=>array( &$this, 'extra_head' ) );
        parent::__construct();
    }


    function extra_head()
    {
?>
<script type="text/javascript" src="/js/jquery-1.3.2.min.js"></script>
<script type="text/javascript" src="/js/jquery.form.js"></script>
<script type="text/javascript" src="/js/jl-fancyforms.js"></script>
<script type="text/javascript">
    $(document).ready( function() {
/*        fancyForms( '.education', {plusLabel:'Add education'}  ); */
    });
</script>
<?php
    }




    function handleActions()
    {
        // submitting new entries?
        $action = get_http_var( "action" );
        if( $action == "submit" ) {
            $added = $this->handleSubmit();
        }

        if( get_http_var('remove_id') ) {
            $this->handleRemove();
        }

        if( $action != 'edit' && $action != 'new' )
        {
            header( "Location: /{$this->journo['ref']}" );
            exit;
        }
        return TRUE;
    }


    function display()
    {
        $action = get_http_var( "action" );

        if( $action=='edit' )
        {
            $edu_id = get_http_var('id');
            $edu = db_getRow( "SELECT * FROM journo_education WHERE journo_id=? AND id=?",
                $this->journo['id'], $edu_id );
?>
<h2>Edit education</h2>
<?php
            $this->showForm( $edu );
        }

        if( $action=='new' )
        {
?>
<h2>Add education</h2>
<?php
            $this->showForm( null );
        }

    }



    function ajax()
    {
/*
        $action = get_http_var( "action" );
        if( $action == "submit" ) {
            $entry_id = $this->handleSubmit();
            $result = array(
                'id'=>$entry_id,
                'editlinks_html'=>$this->genEditLinks($entry_id),
            );
            return $result;
        }
        if( get_http_var("remove_id") )
        {
            $this->handleRemove();
            return array();
        }
*/
        return NULL;
    }



    /* if $edu is null, then display a fresh form for entering a new entry */
    function showForm( $edu )
    {
        static $uniq=0;
        ++$uniq;
        $formtype = 'edit';
        if( is_null( $edu ) ) {
            $edu = array( 'school'=>'', 'field'=>'', 'qualification'=>'', 'year_from'=>'', 'year_to'=>'' );
            $formtype = 'new';
        }

        $formclasses = 'education';

?>

<form class="<?= $formclasses; ?>" method="POST" action="<?= $this->pagePath; ?>">
<div class="field">
  <label for="school_<?= $uniq; ?>">School name</label>
    <input type="text" size="60" name="school" id="school_<?= $uniq; ?>" value="<?= h($edu['school']); ?>" />
    <span class="explain">eg: "St. Cedd's College, Cambridge"</span>
</div>

<div class="field">
  <label for="field_<?= $uniq; ?>">Field(s) of study</label>
   <input type="text" size="60" name="field" id="field_<?= $uniq; ?>" value="<?= h($edu['field']); ?>" />
   <span class="explain">eg: "Rocket Surgery"</span>
</div>

<div class="field">
  <label for="qualification_<?= $uniq; ?>">Qualification</label>
   <input type="text" size="30" name="qualification" id="qualification_<?= $uniq; ?>" value="<?= h($edu['qualification']); ?>" />
   <span class="explain">eg: "BA"</span>
</div>

<fieldset class="field range">
 <span class="faux-label">Years attended</span>
 <label for="year_from_<?= $uniq; ?>">from</label>
 <input type="text" class="year" size="4" name="year_from" id="year_from_<?= $uniq; ?>" value="<?= h($edu['year_from']); ?>" />
 <label for="year_to_<?= $uniq; ?>">to</label>
 <input type="text" class="year" size="4" name="year_to" id="year_to_<?= $uniq; ?>" value="<?= h($edu['year_to']); ?>" />
</fieldset>

<input type="hidden" name="ref" value="<?=$this->journo['ref'];?>" />
<input type="hidden" name="action" value="submit" />
<button class="submit" type="submit">Save</button>
<?php if( $formtype=='edit' ) { ?>
<input type="hidden" name="id" value="<?= $edu['id']; ?>" />
<?= $this->genEditLinks($edu['id']); ?>
<?php } ?>
</form>
<?php if( $formtype=='edit' ) { ?>
<a href="<?= $this->pagePath ?>?ref=<?= $this->journo['ref'] ?>&remove_id=<?= h($edu['id']); ?>">Delete this education</a>
<?php } ?>
<?php

    }



    function handleSubmit()
    {
        $fieldnames = array( 'school', 'field', 'qualification', 'year_from', 'year_to' );
        $item = $this->genericFetchItemFromHTTPVars( $fieldnames );
        if( !$item['year_from'] )
            $item['year_from'] = NULL;
        if( !$item['year_to'] )
            $item['year_to'] = NULL;
        $this->genericStoreItem( "journo_education", $fieldnames, $item );
        return $item['id'];
    }



    function handleRemove() {
        $id = get_http_var("remove_id");

        // include journo id, to stop people zapping other journos entries!
        db_do( "DELETE FROM journo_education WHERE id=? AND journo_id=?", $id, $this->journo['id'] );
        eventlog_Add( 'remove-education', $this->journo['id'] );
        db_commit();
    }


}




$page = new EducationPage();
$page->run();


