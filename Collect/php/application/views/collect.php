<script src="<?=Kohana::$base_url?>js/grid.locale-ru.js" type="text/javascript"></script>
<script src="<?=Kohana::$base_url?>js/jquery.jqGrid.min.js" type="text/javascript"></script>

<div id="toppane"></div>
<div class="jumbotron">
  <h1>Список операций</h1>
</div>  

<?php if (isset($error)): ?>
<div class="alert alert-danger" role="alert">
<?=$error?>
</div>
<? endif; ?>

<div class="row">
  <div class="col-md-12">
    <div class="panel panel-default">
        <div class="panel-heading ">
            <span class="glyphicon glyphicon-circle-arrow-down"></span> Параметры:
        </div>
        <div class="panel-body">
            <form class="form-horizontal" method="post">
              <div class="form-group">
                <label for="link" class="col-sm-2 control-label">Ссылка фильтра: </label>
                <div class="col-sm-10">
                  <input type="text" class="form-control" name="link" id="link" value="<?=$link?>">
                </div>
              </div>
              <div class="form-group">
                <label for="secret" class="col-sm-2 control-label">Секрет: </label>
                <div class="col-sm-10">
                  <input type="password" class="form-control" name="secret" id="secret" value="<?=$secret?>">
                </div>
              </div>
              <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                  <button type="submit" class="btn btn-default">Загрузить</button>
                </div>
              </div>
            </form>        
        </div>
    </div>
  </div>
</div>        
        
<div class="row">
  <div class="col-md-12">
    <div class="panel panel-default">
        <div class="panel-heading ">
            <span class="glyphicon glyphicon-circle-arrow-down"></span> Результат: 
        </div>
        <div class="panel-body">
        
<div>Группировать: <select id="chngroup">
	<option data-show="0,1,1" value="oper_currency" selected="selected">Валюта</option>
	<option data-show="0,0,1" value="oper_currency,oper_group">Валюта, Группа</option>
    <option data-show="0,0,1" data-order="asc,desc" value="oper_currency,oper_mnth">Валюта, Месяц</option>
    <option data-show="0,0,0" data-order="asc,desc,asc" value="oper_currency,oper_mnth,oper_group">Валюта, Месяц, Группа</option>
    <option data-show="0,0,0" data-summary="0,0,1" data-order="asc,asc,desc" value="oper_currency,oper_group,oper_mnth">Валюта, Группа, Месяц</option>
    <option data-show="0,0,1" data-summary="0,1" data-order="asc,desc" value="type,oper_mnth">Источник, Месяц</option>
    <option data-show="0,0,1" data-summary="0,1" data-order="asc,asc" value="type,oper_group">Источник, Группа</option>
	<option value="clear">Убрать группировку</option>	
</select><br><br></div>
        
            <table id="list483"></table>
            <div id="plist483"></div>
        
<script type="text/javascript">

function type_image(cellvalue, options, rowObject) {
    return "<img src='<?=Kohana::$base_url?>img/collect/"+cellvalue+".png' alt='my image' />";
}
function type_mcc(cellvalue, options, rowObject) {
    return '<span title="'+rowObject.mcc_desc+'">'+cellvalue+'</span>';
}
jQuery("#list483").jqGrid({
    url:'<?=Kohana::$base_url?>collect/ajax/?secret=<?=$secret?>',
	datatype: "json",
	height: 'auto',
	rowNum: 500,
	rowList: [250,500,750,1000,1500],
   	colNames:['', 'Группа', 'Валюта', 'Дата', 'Месяц', 'Описание', 'Сумма', 'Кэшбэк', 'MCC'],
   	colModel:[
        {name:'type',index:'type',width:25,formatter:type_image},
   		{name:'oper_group',index:'oper_group', width:250},
   		{name:'oper_currency',index:'oper_currency', width:40},
   		{name:'oper_date',index:'oper_date', width:100, sorttype:"date", formatter:"date"},
        {name:'oper_mnth',index:'oper_mnth',width:100},
   		{name:'oper_description',index:'oper_description', width:450, summaryTpl:"({0}) шт."},
   		{name:'oper_sum',index:'oper_sum', width:80, align:"right",sorttype:"float", formatter:"number", summaryType:'sum'},		
   		{name:'oper_cashback',index:'oper_cashback', width:80,align:"right",sorttype:"float", formatter:"number", summaryType:'sum'},		
   		{name:'oper_mcc',index:'oper_mcc', width:50, formatter:type_mcc}		
   	],
   	pager: "#plist483",
   	viewrecords: true,
   	sortname: 'oper_date',
    sortorder: "desc",
   	grouping:true,
   	groupingView : {
   		groupField : ['oper_currency'],
   		groupColumnShow : [true],
   		groupText : ['<b>{0} - {1} шт.</b>', '<b>{0} - {1} шт.</b>', '<b>{0} - {1} шт.</b>'],
        groupOrder: ['asc'],
        showSummaryOnHide: false, //true,
        groupCollapse: true,
        groupSummary : [true]
   	},
   	caption: "Операции"
});

$(function() {
    jQuery("#chngroup").change(function(){
        var vl = $(this).val();
        
        var ord = $(this).find('option[value="'+vl+'"]').data('order');
        if (typeof ord == "undefined") ord = 'asc,asc,asc';
        
        var sum = $(this).find('option[value="'+vl+'"]').data('summary');
        if (typeof sum == "undefined") sum = '1,1,1';
        sum = sum.split(",");
        sum.forEach(function(a,b) {sum[b] = a=="1"});
        
        var show = $(this).find('option[value="'+vl+'"]').data('show');
        if (typeof show == "undefined") show = '1,1,1';
        show = show.split(",");
        show.forEach(function(a,b) {show[b] = a=="1"});
        
        if(vl) {
            if(vl == "clear") {
                jQuery("#list483").jqGrid('groupingRemove',true);
            } else {
                var GroupOption = new Object();
                GroupOption.groupField = vl.split(",");
                //GroupOption.groupColumnShow = show;
                GroupOption.groupOrder = ord.split(",");
                GroupOption.groupSummary = sum;
                GroupOption.groupCollapse = true; //false;
                GroupOption.showSummaryOnHide = false; //true;
                GroupOption.groupText = ['<strong> {0} - {1} шт.</strong>']
                $("#list483").setGridParam({groupingView : GroupOption});
                jQuery("#list483").jqGrid('groupingGroupBy',vl.split(","));
            }
        }
    });
});
</script>
        
        </div>
    </div>
</div>
</div>

<div class="row">
  <div class="col-md-12">
	<div class="panel panel-default">
        <div class="panel-heading ep-handle" id="sample">
            <span class="glyphicon glyphicon-circle-arrow-down"></span> Как пользоваться:
        </div>
        <div class="panel-body">
			<p>В процессе.</p>
		</div>
	</div>
</div>
</div>
