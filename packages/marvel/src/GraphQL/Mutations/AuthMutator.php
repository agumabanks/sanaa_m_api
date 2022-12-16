<?php


namespace Marvel\GraphQL\Mutation;


use Marvel\Facades\Shop;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class AuthMutator
{
    public function token($rootValue, array $args, GraphQLContext $context)
    {
        return Shop::call('Marvel\Http\Controllers\UserController@token', $args);
    }

    public function logout($rootValue, array $args, GraphQLContext $context)
    {
        return Shop::call('Marvel\Http\Controllers\UserController@logout', $args);
    }

    public function register($rootValue, array $args, GraphQLContext $context)
    {
        return Shop::call('Marvel\Http\Controllers\UserController@register', $args);
    }
    public function changePassword($rootValue, array $args, GraphQLContext $context)
    {
        return Shop::call('Marvel\Http\Controllers\UserController@changePassword', $args);
    }
    public function forgetPassword($rootValue, array $args, GraphQLContext $context)
    {
        return Shop::call('Marvel\Http\Controllers\UserController@forgetPassword', $args);
    }
    public function verifyForgetPasswordToken($rootValue, array $args, GraphQLContext $context)
    {
        return Shop::call('Marvel\Http\Controllers\UserController@verifyForgetPasswordToken', $args);
    }
    public function resetPassword($rootValue, array $args, GraphQLContext $context)
    {
        return Shop::call('Marvel\Http\Controllers\UserController@resetPassword', $args);
    }
    public function banUser($rootValue, array $args, GraphQLContext $context)
    {
        return Shop::call('Marvel\Http\Controllers\UserController@banUser', $args);
    }
    public function activeUser($rootValue, array $args, GraphQLContext $context)
    {
        return Shop::call('Marvel\Http\Controllers\UserController@activeUser', $args);
    }
    public function contactAdmin($rootValue, array $args, GraphQLContext $context)
    {
        return Shop::call('Marvel\Http\Controllers\UserController@contactAdmin', $args);
    }
    public function socialLogin($rootValue, array $args, GraphQLContext $context)
    {
        return Shop::call('Marvel\Http\Controllers\UserController@socialLogin', $args);
    }
    public function sendOtpCode($rootValue, array $args, GraphQLContext $context)
    {
        return Shop::call('Marvel\Http\Controllers\UserController@sendOtpCode', $args);
    }
    public function verifyOtpCode($rootValue, array $args, GraphQLContext $context)
    {
        return Shop::call('Marvel\Http\Controllers\UserController@verifyOtpCode', $args);
    }
    public function otpLogin($rootValue, array $args, GraphQLContext $context)
    {
        return Shop::call('Marvel\Http\Controllers\UserController@otpLogin', $args);
    }
    public function updateContact($rootValue, array $args, GraphQLContext $context)
    {
        return Shop::call('Marvel\Http\Controllers\UserController@updateContact', $args);
    }
    public function addPoints($rootValue, array $args, GraphQLContext $context)
    {
        return Shop::call('Marvel\Http\Controllers\UserController@addPoints', $args);
    }
    public function makeOrRevokeAdmin($rootValue, array $args, GraphQLContext $context)
    {
        return Shop::call('Marvel\Http\Controllers\UserController@makeOrRevokeAdmin', $args);
    }
    public function generateDownloadableUrl($rootValue, array $args, GraphQLContext $context)
    {
        return Shop::call('Marvel\Http\Controllers\UserController@generateDownloadableUrl', $args);
    }
    public function generateOrderExportUrl($rootValue, array $args, GraphQLContext $context)
    {
        return Shop::call('Marvel\Http\Controllers\OrderController@exportOrderUrl', $args);
    }

    public function subscribeToNewsletter($rootValue, array $args, GraphQLContext $context)
    {
        return Shop::call('Marvel\Http\Controllers\UserController@subscribeToNewsletter', $args);
    }
}