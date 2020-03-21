import Vue from 'vue';
import VueRouter from 'vue-router';

import About from './components/cms_pages/About';
import AdminPanel from './components/admin/AdminPanel';
import AdminUsers from './components/admin/Users';
import Home from './components/cms_pages/Home';
import Login from './components/basic/Login';
import Messages from './components/users/Messages';
import Register from './components/basic/Register';
import User from './components/basic/User';
import UserSettings from './components/basic/UserSettings';
import _404 from './components/basic/404';

Vue.use(VueRouter);

const router = new VueRouter({
    mode: 'history',
    routes: [
        {
            path: '/',
            name: 'home',
            component: Home,
        },
        {
            path: '/admin',
            name: 'Admin',
            component: AdminPanel,
        },
        {
            path: '/admin/users',
            name: 'Users',
            component: AdminUsers
        },
        {
            path: '/about',
            name: 'about',
            component: About,
        },
        {
            path: '/login',
            name: 'login',
            component: Login,
        },
        {
            path: '/messages',
            name: 'messages',
            component: Messages,
        },
        {
            path: '/register',
            name: 'register',
            component: Register,
        },
        {
            path: '/user/:id',
            name: 'user',
            component: User,
        },
        {
            path: '/settings',
            name: 'user-settings',
            component: UserSettings,
        },
        {
            path: '*',
            name: 'no-route',
            component: _404
        }
    ]
});

export default router;