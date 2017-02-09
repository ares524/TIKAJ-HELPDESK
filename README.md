# laravel-repository-query
> This package support Repository Parttern in Laravel.
> We create a parameter named 'filters' in our GET url.
> The Query Builder instance can accept it as where conditions so we don't have to write it in every modules.

### Install
Run the following command from you terminal:

    composer require "guosheng1987/laravel-repository-query: 0.*"

or add this to require section in your composer.json file:
    
    "laravel-repository-query/repositories": "0.*"

last command 

    composer update

### Demo
A simple demo could help us unserstand this library,Let's assume that you start to design your api which shows your user in diffent enterprises that contains several departments and positions.
The relationship is Enterprises has some Departments,Department has some Positions,and Users belows to one of them,so we bulid four models '**User**','**Department**','**Position**','**Enterprise**' here

At first,in the route file, we find *web.php* and write a simple Resource Route here.(Laravel 5.2 or lower version also similiar)

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

            return view('user.index', ['user' => $user]);
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