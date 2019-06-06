<?php
/**
 * @author joel diaz
*/
namespace App\Http\Controllers;

use App\Jobs\Job;
use App\Models\Model;
use Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Request;
use Illuminate\Http\Request as HttpRequest;
use Input;
use Storage;

/**
 * Base controller provided by Laravel
 *
 * Methods all extended for general controller use
 */
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Get a models parameters from available input fields
     * @param  Model  $mod The model we are using
     * @return array       The parameters we need and expect for the model
     */
    public function getModelParamsAsArray(HttpRequest $request, Model $mod)
    {
        $params = [];

        foreach ($mod->getColumns() as $field) {
            if ($request->filled($field)) {
                $params[$field] = $request->input($field);
            }
        }

        return $params;
    }

    /**
     * Get an array containing necessary request and user information for error reporting
     * @return array Request and user data used for error reporting
     */
    public function getRequestDataForErrorReporting()
    {
        $userIp = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] :
        isset($_SERVER['HTTP_X_REAL_IP']) ? $_SERVER['HTTP_X_REAL_IP'] :
        isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '[unknown]';

        return [
            'displayName' => $this->getUser(),
            'uri'         => Request::fullUrl(),
            'method'      => Request::method(),
            'userEmail'   => Auth::user() ? Auth::user()->email : '[unknown]',
            'userIp'      => $userIp,
            'email'       => array(
                'cahpsengineers@axxess.com',
            ),
            'name'        => array(
                'CAHPS Engineers',
            ),
            'httpReferer' => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null,
            'userAgent'   => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null,
        ];
    }

    /**
     * Get the name of the logged in user
     * @return string The user who is logged in
     */
    protected function getUser()
    {
        return Auth::user() ? Auth::user()->display_name : '[unknown]';
    }

    /**
     * Get a file size in human readable format
     * @param  integer $bytes    file size in bytes
     * @param  integer $decimals precision points
     * @return string            the human readable file size
     */
    protected function human_filesize($bytes, $decimals = 2)
    {
        $sz     = 'BKMGTP';
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
    }

    /**
     * For some root directory in storage, where we cant be certain of casing
     * check that the file exists
     * @param  [type] $root     [description]
     * @param  [type] $filename [description]
     * @return [type]           [description]
     */
    public function storageFileExists($root, $filename)
    {

        $parts      = explode(".", $filename);
        $fileExists = false;
        if (Storage::has("$root$filename")
            || Storage::has($root . $parts[0] . "." . strtolower($parts[1]))
            || Storage::has($root . $parts[0] . "." . strtoupper($parts[1]))
            || Storage::has($root . strtoupper($parts[0]) . "." . $parts[1])
            || Storage::has($root . strtolower($parts[0]) . "." . $parts[1])
            || Storage::has($root . strtoupper($parts[0]) . "." . strtolower($parts[1]))
            || Storage::has($root . strtolower($parts[0]) . "." . strtolower($parts[1]))
            || Storage::has($root . strtoupper($parts[0]) . "." . strtoupper($parts[1]))
            || Storage::has($root . strtolower($parts[0]) . "." . strtoupper($parts[1]))
        ) {
            $fileExists = true;
        }
        return $fileExists;
    }

    /**
     * Method to pass a job along to for dispatching
     * @param  App\Jobs\Job    $job A job to be dispatched
     * @return void
     */
    public function dispatchJob(Job $job)
    {
        $this->dispatch($job);
    }

}
