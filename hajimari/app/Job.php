<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Job extends Model
{
    protected $table = 'jobs';


    public static function getFieldParent($id)
    {
    	return DB::table('job_category')->where('parent_id',$id)->get();
    }

    public static function search($inputs,$page=1)
    {
    	$query = self::query();
    	$query->select('job_category.name as job_category_name','jobs.name','area.name as area_name','jobs.lang_id','jobs.salary_from','jobs.salary_to')
            ->leftJoin('job_category', function($join) {
                $join->on('job_category.id', '=', 'jobs.category_id');

            })->leftJoin('area','area.id','=','jobs.location');


      	if(!empty($inputs['nameJob'])){
      		$query->where('jobs.name','LIKE','%'.$inputs['nameJob'].'%');
        }

        if(!empty($inputs['workplace'])){
        	$query->whereIn('location',$inputs['workplace']);
        }

        
        if(!empty($inputs['field_search'])){

            $listParent_id = DB::table('job_category')->whereIn('id',$inputs['field_search'])->where('parent_id',0)->pluck('id')->toArray();

           if(!empty($listParent_id)){
                $listCategory = DB::table('job_category')->whereIn('parent_id',$listParent_id)->pluck('id')->toArray();

                $query->whereIn('category_id',$listCategory);
           }

           $query->whereIn('category_id',$inputs['field_search'],'or');
        }

        if(!empty($inputs['salaryLevel'])){
        	$salary_level = $inputs['salaryLevel'];

        	
        	for($i=0; $i<count($salary_level);$i++){

        		static::SelectSalaryLevel($i,$salary_level,$query);
        		
        	}
        	
        }

        if(!empty($inputs['laguageLevel'])){
        	$query->whereIn('lang_id',$inputs['laguageLevel']);
        }

        $total_record_per_page = 10;

        // $listJob = $query->paginate(10); 
        $TotalListJob = $query->count();
        $from_page = ($page - 1)*$total_record_per_page;
        $listJob = $query->offset($from_page)->limit($total_record_per_page)->get();

		if($page == 1) $listJob['totalRecord'] = $query->count();

        return $listJob;
    }

    public static function SelectSalaryLevel($i,$salary_level,$query){
    	if($i==0){  // 0 -> where , 1-> orWhere
			if($salary_level[$i]==1){
				$query->where('salary_to','<',500);
			}
			else if($salary_level[$i]==2){
				$query->where('salary_from','>=',500)->where('salary_to','<=',1000);
			}
			else if($salary_level[$i]==3){
				$query->where('salary_from','>',1000)->where('salary_to','<=',1500);
			}
			else if($salary_level[$i]==4){
				$query->where('salary_from','>',1500)->where('salary_to','<=',2000);
			}
			else if($salary_level[$i]==5){
				$query->where('salary_from','>',2000)->where('salary_to','<=',2500);
			}
			else if($salary_level[$i]==6){
				$query->where('salary_from','>',2500);
			}
        			
		}else{
			if($salary_level[$i]==1){
				$query->Orwhere('salary_from','<',500);
			}
			else if($salary_level[$i]==2){
				$query->Orwhere('salary_from','>=',500)->Orwhere('salary_to','<=',1000);
			}
			else if($salary_level[$i]==3){
				$query->Orwhere('salary_from','>',1000)->Orwhere('salary_to','<=',1500);
			}
			else if($salary_level[$i]==4){
				$query->Orwhere('salary_from','>',1500)->Orwhere('salary_to','<=',2000);
			}
			else if($salary_level[$i]==5){
				$query->Orwhere('salary_from','>',2000)->Orwhere('salary_to','<=',2500);
			}
			else if($salary_level[$i]==6){
				$query->Orwhere('salary_from','>',2500);
			}

			
		}

    }

    public static function getListLanguage()
    {
        return self::orderBy('lang_id','asc')->distinct()->pluck('lang_id')->reject(function ($value) {
            return !$value;  // remove value 0,null
        });
    }
}


