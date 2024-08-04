<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class PrPoCerController extends Controller
{
    public function getAllUsers()
    {
        $query = Db::select("select  * from table_pr");
        $data_json_queryPr = ($query);

        $query = Db::select("select  * from table_po;");
        $data_json_queryPo = ($query);

        $query = Db::select("select  * from table_cer");
        $data_json_queryCer = ($query);

        $query = Db::select("select  * from table_po_line");
        $data_json_queryPoLine = ($query);

        $query = Db::select("select  * from table_cer_line");
        $data_json_queryCerLine = ($query);

        $sqlCode = <<<SQL
                        select 
                            count(distinct po.id) total_po,
                            count(distinct cer.id) total_cer
                        from 
                            table_pr pr
                        left join 
                            table_po po on pr.id = po.pr_id
                        left join 
                            table_cer cer on pr.id = cer.pr_id;
                     SQL;
        $query = Db::select($sqlCode);
        $data_json_queryCountPoCerInPr = ($query);

        $sqlCode = <<<SQL
                        select 
                            pr.code pr_code,
                            count(distinct po.id) total_po,
                            count(distinct cer.id) total_cer
                        from 
                            table_pr pr
                        left join 
                            table_po po on pr.id = po.pr_id
                        left join 
                            table_cer cer on pr.id = cer.pr_id
                        group by 
                            pr.code;
                    SQL;
        $query = Db::select($sqlCode);
        $data_json_queryBreakdownCountPoCerInPr = ($query);

        $sqlCode = <<<SQL
                        select po.pr_id , sum(pol.quantity ) as po_quantity
                        from table_po po
                        left join table_po_line pol
                        on po.id = pol.po_id 
                        group by po.pr_id
                    SQL;
        $query = Db::select($sqlCode);
        $data_json_queryPoPoLine = ($query);          

        $sqlCode = <<<SQL
                        select cer.pr_id , sum(cerl.quantity ) as cer_quantity
                        from table_cer cer
                        left join table_cer_line cerl
                        on cer.id = cerl.cer_id 
                        group by cer.pr_id
                    SQL;
        $query = Db::select($sqlCode);
        $data_json_queryCerCerLine = ($query);        

        $sqlCode = <<<SQL
                        select 	
                            pr.id, 
                            pr.code pr_code , 
                            po_sum.po_quantity, 
                            cer_sum.cer_quantity,
                            case
                                when ifnull(po_sum.po_quantity, 0) = ifnull(cer_sum.cer_quantity, 0)
                                then 'Total Qty Match'
                                else 'Total Qty Mismatch'
                            end result
                        from table_pr pr
                        left join
                        (
                            select po.pr_id , sum(pol.quantity ) as po_quantity
                            from table_po po
                            left join table_po_line pol
                            on po.id = pol.po_id 
                            group by po.pr_id
                        ) po_sum
                        on po_sum.pr_id = pr.id
                        left join
                        (
                            select cer.pr_id , sum(cerl.quantity ) as cer_quantity
                            from table_cer cer
                            left join table_cer_line cerl
                            on cer.id = cerl.cer_id 
                            group by cer.pr_id
                        ) cer_sum
                        on cer_sum.pr_id = pr.id;
                    SQL;
        $query = Db::select($sqlCode);
        $data_json_queryCompareTally = ($query);  
        
        $sqlCode = <<<SQL
                        select 
                        pr.code pr_code,
                        po.code po_code,
                        cer.code cer_code,
                        pol.sequence line_sequence,
                        pol.product_id line_product_id,
                        pol.quantity po_line_quantity,
                        cerl.quantity cer_line_quantity,
                        case
                            when ifnull(pol.quantity, 0) = ifnull(cerl.quantity, 0)
                            then 'Qty Match'
                            else 'Qty Mismatch'
                        end result,
                        'both PO and CER exist' remark
                        from table_pr pr
                        left join table_po po on pr.id = po.pr_id
                        left join table_cer cer on pr.id = cer.pr_id
                        left join table_po_line pol on po.id = pol.po_id
                        left join table_cer_line cerl on cer.id = cerl.cer_id
                        where pol.product_id = cerl.product_id 
                        and pol.sequence = cerl.sequence
                        -- kita union dengan query yg lain
                        union
                        -- query untuk check dimana sequence & product_id ada di PO tapi tidak ada di CER
                        select
                        pr.code pr_code,
                        po.code po_code,
                        cer.code cer_code,
                        pol.sequence line_sequence,
                        pol.product_id line_product_id,
                        pol.quantity po_line_quantity,
                        cerl.quantity cer_line_quantity,
                        'Qty Mismatch' result,
                        'only PO line exist' remark
                        from table_pr pr
                        left join table_po po on pr.id = po.pr_id
                        left join table_cer cer on pr.id = cer.pr_id
                        left join table_po_line pol on po.id = pol.po_id
                        left join table_cer_line cerl on cer.id = cerl.cer_id 
                        and pol.product_id = cerl.product_id 
                        and pol.sequence = cerl.sequence
                        where cerl.quantity is null 
                        and cerl.sequence is null 
                        and pol.quantity is not null
                        and pol.sequence is not null
                        -- kita union dengan query yg lain
                        union
                        -- query untuk check dimana sequence & product_id ada di CER tapi tidak ada di PO
                        select
                        pr.code pr_code,
                        po.code po_code,
                        cer.code cer_code,
                        cerl.sequence line_sequence,
                        cerl.product_id line_product_id,
                        pol.quantity po_line_quantity,
                        cerl.quantity cer_line_quantity,
                        'Qty Mismatch' result,
                        'only CER line exist' remark
                        from table_pr pr
                        left join table_po po on pr.id = po.pr_id
                        left join table_cer cer on pr.id = cer.pr_id
                        left join table_cer_line cerl on cer.id = cerl.cer_id 
                        left join table_po_line pol on po.id = pol.po_id
                        and pol.product_id = cerl.product_id 
                        and pol.sequence = cerl.sequence
                        where cerl.quantity is not null 
                        and cerl.sequence is not null 
                        and pol.quantity is null
                        and pol.sequence is null;
                    SQL;
        $query = Db::select($sqlCode);
        $data_json_queryCompareItemPoCerSame = ($query);  

        $sqlCode = <<<SQL
                        select count(*) total_pr from table_pr pr;
                    SQL;
        $query = Db::select($sqlCode);
        $data_json_querySummaryTotalPr =  $query[0]->total_pr;

        $sqlCode = <<<SQL
                        select count(po.id) total_po from table_pr pr 
                        left join table_po po 
                        on pr.id = po.pr_id 
                        where po.id is not null;
                    SQL;
        $query = Db::select($sqlCode);
        $data_json_querySummaryTotalPo = $query[0]->total_po;


        $sqlCode = <<<SQL
                        select count(cer.id) total_cer from table_pr pr 
                        left join table_cer cer 
                        on pr.id = cer.pr_id 
                        where cer.id is not null;
                    SQL;
        $query = Db::select($sqlCode);
        $data_json_querySummaryTotalCer = $query[0]->total_cer;

        $sqlCode = <<<SQL
                        select sum(summary.po_quantity) total_item from 
                        ( select 	
                            pr.id, 
                            pr.code pr_code , 
                            po_sum.po_quantity, 
                            cer_sum.cer_quantity,
                            case
                                when ifnull(po_sum.po_quantity, 0) = ifnull(cer_sum.cer_quantity, 0)
                                then 'Total Qty Match'
                                else 'Total Qty Mismatch'
                            end result
                        from table_pr pr
                        left join
                        (
                            select po.pr_id , sum(pol.quantity ) as po_quantity
                            from table_po po
                            left join table_po_line pol
                            on po.id = pol.po_id 
                            group by po.pr_id
                        ) po_sum
                        on po_sum.pr_id = pr.id
                        left join
                        (
                            select cer.pr_id , sum(cerl.quantity ) as cer_quantity
                            from table_cer cer
                            left join table_cer_line cerl
                            on cer.id = cerl.cer_id 
                            group by cer.pr_id
                        ) cer_sum
                        on cer_sum.pr_id = pr.id
                        ) summary
                        where result = 'Total Qty Match'
                    SQL;
        $query = Db::select($sqlCode);
        $data_json_queryTotalTally = ($query);
        
        $finalResult = [
                            'data_json_queryPr' => $data_json_queryPr ,
                            'data_json_queryPo' => $data_json_queryPo ,
                            'data_json_queryCer' => $data_json_queryCer ,
                            'data_json_queryPoLine' => $data_json_queryPoLine,
                            'data_json_queryCerLine' => $data_json_queryCerLine,
                            'data_json_queryCountPoCerInPr' => $data_json_queryCountPoCerInPr,
                            'data_json_queryBreakdownCountPoCerInPr' => $data_json_queryBreakdownCountPoCerInPr,
                            'data_json_queryPoPoLine' => $data_json_queryPoPoLine,
                            'data_json_queryCerCerLine' => $data_json_queryCerCerLine,
                            'data_json_queryCompareTally' => $data_json_queryCompareTally,
                            'data_json_queryCompareItemPoCerSame' => $data_json_queryCompareItemPoCerSame,
                            'data_json_querySummaryTotalPr' => $data_json_querySummaryTotalPr,
                            'data_json_querySummaryTotalPo' => $data_json_querySummaryTotalPo,
                            'data_json_querySummaryTotalCer' => $data_json_querySummaryTotalCer,
                            'data_json_queryTotalTally' => $data_json_queryTotalTally

                        ];
        return ['data' => $finalResult];
    }
    

    
}
