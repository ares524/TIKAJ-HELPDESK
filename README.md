# laravel-repository-query
> This package support Repository Parttern in Laravel.
> We create a parameter named 'filters' in our GET url.
> The Query Builder instance can accept it as where conditions so we don't have to write it in every modules.

### Install


### Demo

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

    
        public function index($id)
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
            $builder = (new User)->newQuery();

            $user = $this->getPaginate($builder);

            return $user;
        }
    }

After that,let's see our url <http://yoursite.dev/user?&filters[username][like]=abc&filters[gender][equal]=1>.Now the $user pagination result was filtered by **username** and **gender** fields in the User model.
You know what I am doing now. Enjoy !!!