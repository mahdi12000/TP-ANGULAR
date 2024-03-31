<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Emprunt;
use App\Models\Livre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

class userController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'c_password' => 'required|same:password',
            'city' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->all(),
                'BoolenMessage' => false
            ]);
        } else {
            $user = User::create([
                'firstname' => $data['firstname'],
                'lastname' => $data['lastname'],
                'email' => $data['email'],
                'password' => bcrypt($data['password']),
                'city' => $data['city']
            ]);
            $token = $user->createToken('API_Token')->plainTextToken;
            return response()->json([
                'BoolenMessage' => true,
                'token' => $token
            ]);
        }
    }

    public function login(Request $request)
    {
        //validation of connection data 
        $data = $request->all();
        $validator = Validator::make($data, [
            'email' => 'required|email|string',
            'password' => 'required|string|min:8'
        ]);
        //if validation fails
        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->all(),
                'validator' => false
            ]);
        }

        // connection attempt 
        if (Auth::attempt(['email' => $data['email'], 'password' => $data['password']])) {
            $user = User::where('email', $data['email'])->first();
            $livresNonEmpruntes = Livre::whereNotIn('id', function ($query) {
                $query->select('idLivre')->from('emprunts');
            })->get();
            $token = $user->createToken('rememberToken')->plainTextToken;
            return response()->json([
                'API_Token' => $token,
                'LoginDataCorrect' => true,
                'validator' => true,
                'user' => $user,
                'livresNonEmpruntes' => $livresNonEmpruntes
            ]);
        } else {
            return response()->json([
                'LoginDataCorrect' => false,
                'validator' => true
            ]);
        }
    }

    public function log_out()
    {
        try {
            $user_id = Auth::user()->id;
            $user = User::where('id', $user_id)->first();
            $user->tokens()->delete();
            return response()->json(['boolMsg' => true]);
        } catch (\Exception $e) {
            return response()->json(['msg' => $e->getMessage(), 'boolMsg' => false]);
        }
    }

    public function emprunter(Request $request)
    {
        $Data = $request->all();
        $idLivre = $Data['id'];
        $livre = Livre::find($idLivre);
        $newEmprunt = new Emprunt();
        $newEmprunt->idUser = Auth::user()->id;
        $newEmprunt->idLivre = $idLivre;
        $newEmprunt->save();
        return response()->json(['emprunteAvecSucces' => true], 200);
    }

    public function desemprunter(Request $request)
    {
        $Data = $request->all();
        $idLivre = $Data['id'];
        if ($idLivre) {
            $emprunt = Emprunt::where('idLivre', $idLivre)->first();
            $emprunt->delete();
            return response()->json(['desemprunteAvecSucces' => true], 200);
        } else return response()->json(['emprunteAvecSucces' => false], 402);
    }

    public function updateUserdata(Request $request)
    {
        try {
            $User_id = Auth::user()->id;
            $User = User::find($User_id);
            $newData = $request->all();
            if (!empty($newData['firstname'])) {
                $User->firstname = $newData['firstname'];
                $User->save();
            }
            if (!empty($newData['lastname'])) {
                $User->lastname = $newData['lastname'];
                $User->save();
            }
            if (!empty($newData['city'])) {
                $User->city = $newData['city'];
                $User->save();
            }
            return response()->json(['message' => "updated successfully", "boolMsg" => true]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), "boolMsg" => false], 402);
        }
    }

    public function updatePassword(Request $request)
    {
        $message = "";
        $boolMessage = true;
        $User_id = Auth::user()->id;
        $User = User::find($User_id);
        $newData = $request->all();
        if (!empty($newData['newPassword']) && !empty($newData['oldPassword'])) {
            $oldPassword = $User->password;
            $User->password = bcrypt($newData['newPassword']);
            if (Hash::check($newData['newPassword'], $oldPassword)) {
                $User->password = bcrypt($newData['newPassword']);
                $User->save();
                $message = "updated successfully";
            } else {
                $message = "incorrect password";
                $boolMessage = false;
            }
            return response()->json(["message" => $message, "boolMsg" => $boolMessage]);
        } else return response()->json(['message' => "empty data", 'boolMsg' => false]);
    }

    public function getLivres()
    {
        $livres = Livre::all();
        return response()->json(['livres' => $livres]);
    }

    public function myData()
    {
        $user = Auth::user();
        return response()->json(['user:' => $user]);
    }
}
