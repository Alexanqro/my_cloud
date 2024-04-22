<?php

return [
    '/user/' => [
        'GET' => 'User::showUsers',
        'POST' => 'User::addUser',
        'PUT' => 'User::updateUser',
        'DELETE' => 'User::deleteUser',
    ],
    '/login/' => [
        'POST' => 'User::loginUser',
    ],
    '/logout/' => [
        'GET' => 'User::logoutUser',
    ],
    '/reset-password/' => [
        'POST' => 'User::resetPassword',
    ],
    '/new-password/' => [
        'POST' => 'User::newPassword',
    ],
    '/users/' => [
        'GET' => 'User::getUser',
    ],
    '/admin/user/' => [
        'GET' => 'Admin::showUsers',
        'DELETE' => 'Admin::deleteUser',
        'PUT' => 'Admin::updateUser',
    ],
    '/file/' => [
        'POST' => 'File::addFile',
        'GET' => 'File::showFile',
        'PUT' => 'File::renameMoveFile',
        'DELETE' => 'File::deleteFile',
    ],
    '/directory/' => [
        'POST' => 'File::addFolder',
        'PUT' => 'File::renameFolder',
        'GET' => 'File::getInfoFolder'
    ],
    '/files/share/' => [
        'GET' => 'File::shareUsers',
        'PUT' => 'File::shareFile',
        'DELETE' => 'File::terminateAccessFile'
    ],
];