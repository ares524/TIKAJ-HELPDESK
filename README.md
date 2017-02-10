# laravel-repository-query
> This package support Repository Parttern in Laravel.
> We create a parameter named 'filters' in our GET url.
> The Query Builder instance can accept it as where conditions so we don't have to write it in every modules.

### Require 

* Laravel 5.3 (lower version will add in future)

### Install

Run the following command from you terminal:

    composer require "guosheng1987/laravel-repository-query:0.1.*"

or add this to require section in your composer.json file:
    
    "laravel-repository-query/repositories": "0.1.*"

last command 

    composer update

### Demo
A simple demo could help us unserstand this library,Let's assume that we start to design our api which shows our users in diffent enterprises that contains several departments and positions.
The relationship is Enterprises has some Departments,Department has some Positions,and Users belows to one of them,so we bulid four models '**User**','**Department**','**Position**','**Enterprise**' here

	<?php

	namespace App\Models;

	use Illuminate\Notifications\Notifiable;
	use Illuminate\Foundation\Auth\User as Authenticatable;

	use Hash;

	class User extends Authenticatable
	{
		use Notifiable;

		protected $guarded = ['id'];

		/**
		 * The attributes that are mass assignable.
		 *
		 * @var array
		 */
		//protected $fillable = [
		//    'username', 'password', 'realname', 'phone', 'visit', 'lasttime'
		//];

		/**
		 * The attributes that should be hidden for arrays.
		 *
		 * @var array
		 */
		protected $hidden = [
			'password', 'remember_token',
		];

		/**
		 * Using Hash to encrypt password when User model saved 
		 *
		 * @param  string $value raw password
		 * @return void
		 */
		public function setPasswordAttribute($value)
		{
			$this->attributes['password'] = Hash::needsRehash($value) ? Hash::make($value) : $value;
		}
	}

To follow the laravel docs to fill other models in our code.Here I omitted them.

In the routes folder, we find *web.php* and write a simple Resource Route here.

    Route::resource('user', 'UserController');

I consider Repository as an interactive Data Layer to help us insulate models and controllers ,so here we write a method named 'getUserPaginate' in UserController.
And in Laravel Docs,they do it like this. 

    <?php

    namespace App\Http\Controllers;

    use App\User;
    use App\Repositories\UserRepository;
    use App\Http\Controllers\Controller;

    class UserController extends Controller
    {
        
        protected $users;

        public function __construct(UserRepository $users)
        {
            $this->users = $users;
        }

    
        public function index()
        {
            $user = $this->users->getUserPaginate();
			
			$result = array('msg' => 'success', 'result' => $users->toArray());;
		
			return response()->json($result);
        }
    }

What's our UserRepository?Don't worry,it shows here.

    <?php

    namespace App\Repositories;

    use App\User;
    use RepositoryQuery\Repository;

    class UserRepository extends Repository
    {
        public $query;

        public function getUserPaginate()
        {
            $builder = (new User)->newQuery()
			    ->join('departments as d','users.department_id','=','d.id','LEFT')
			    ->join('positions','users.position_id','=','positions.id','LEFT')
			    ->join('enterprise','users.enterprise_id','=','enterprise.id','LEFT')
			    ->select('users.*');

            $user = $this->getPaginate($builder);

            return $user;
        }
    }

After that,let's see our url <http://yoursite.dev/user?&filters[username][like]=Al&filters[gender][equal]=1&order[username]=asc>.Now the $user pagination result was filtered by **username** and **gender** fields in the User model.
You know what I am doing now. Enjoy !!!