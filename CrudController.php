<?php
	namespace App\Http\Controllers;
	use Illuminate\Http\Request;
	// use Illuminate\Support\Facades\DB;
	use Auth;
	use App\user_details;
	use Response;
	use DB;
	use App\User;
	class CrudController extends Controller
	{
		
		public function __construct()
		{
			
			$this->middleware('auth');          
			
		}
		
		public function index()
		{
			
			
			return view('user_details.user.create');
			
			
			
		}
		
		/**
			* Show the form for creating a new resource.
			*
			* @return \Illuminate\Http\Response
		*/
		public function create()
		{
			
			
			$alluser=DB::table('user_names')
			
			->orderBy('id', 'desc')
			->get();
			
			return view('user_details.user.create', compact('alluser'));
		}
		public function alluser()
		{
			$alluser=DB::table('user_names')
			->select('cat_name','id','status')
			->orderBy('id', 'desc')
			->paginate(5);
			return view('user_details.user.allusers', compact('alluser'));
		}
		public function getuserpagination(request $request)
		{
			if($request->ajax())
			{
				$query = $request->get('query');
				$query = str_replace(" ", "%", $query);
				
				$accid=$this->getaccid(auth()->user()->id);	
				$alluser=DB::table('user_names')
				->select('cat_name','id','status')
				->orWhere(function ($tickets) use ($query) {
					$tickets->orwhere('cat_name', 'like', '%'.$query.'%');
				})->where(['acc_id' => $accid])
				->orderBy('id', 'desc')->paginate(5);
				return view('user_details.user.pagination', compact('alluser'));
			}
		}
		
		
		public function store(Request $request)
		{
			$this->validate($request, [
			'cat_name' => 'required',
			
			],
			[
			'cat_name.required'      => 'user field is required',
			]
			);
			
			
			$users = DB::table('users')
			->where('id','=', auth()->user()->id)
			->first();
			
			$catexist = DB::table('user_names')			
			->where([
			['cat_name', '=', $request->input('cat_name')]
			])->pluck('cat_name')->count();		
			
			if($catexist>0) 
			{
				return back()->withInput()
				->with('msg', 'user name already exist'); 
			}
			
			
			
			$user = user_details::create([
            'cat_name' => $request->get('cat_name'),
            'status' => '1',
			]);	
			return redirect('/getuser')->with('success', 'new user added');
			
		}
		public function show(Employee $teacher)
		{
			//
		}
		
		
		public function edit(request $request)
		{
			$catexist = DB::table('user_names')			
			->where([
			['cat_name', '=', $request->input('cat_name')]
			])->pluck('cat_name')->count();		
			
			
			if($catexist>0) // if anything in user schedule conflicts with what he wants to add to it
			{
				
				$post="exit";
			}
			else{
				
				$post = user_details::find ($request->id);
				$post->cat_name = $request->cat_name;
				$post->save();
			}
			
			return response()->json($post);
		}
		
		
		public function update(Teacher $teacher)
		{
			//
			
			$input = request(['name','experience', 'classroom_id']);
			
			$teacher->fill($input)->save();
			
			return redirect('/teachers');
		}
		
		/**
			* Remove the specified resource from storage.
			*
			* @param  \App\Teacher  $teacher
			* @return \Illuminate\Http\Response
		*/
		public function destroy($id)
		{
			
			user_details::findOrFail($id)->delete();
			return response()->json([
			'success' => 'Record has been deleted successfully!'
			]);
			
			
		}
	
	}
