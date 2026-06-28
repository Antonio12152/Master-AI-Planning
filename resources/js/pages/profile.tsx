import React, { JSX } from 'react';
import { Head, Link, usePage } from '@inertiajs/react';
import { User, Mail, MapPin, Calendar, CheckCircle, Clock, Edit, FolderOpen } from 'lucide-react';
import { Button } from '@/components/ui/button';

interface UserProfile {
    id: number;
    name: string;
    email: string;
    avatar_url?: string;
    bio?: string;
    timezone?: string;
    is_active: boolean;
    is_verified: boolean;
    email_verified_at?: string;
    last_login_at?: string;
    created_at: string;
    updated_at: string;
}

export default function Profile(): JSX.Element {
    const { user } = usePage<{ user: UserProfile }>().props;

    // Debug: Check if user data exists
    if (!user) {
        return (
            <>
                <Head title="My Profile" />
                <div className="min-h-screen bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 flex items-center justify-center">
                    <div className="text-center text-white">
                        <h1 className="text-2xl font-bold">Error loading profile</h1>
                        <p className="text-slate-400">User data not available. Please try logging in again.</p>
                    </div>
                </div>
            </>
        );
    }

    const formatDate = (dateString?: string | null) => {
        if (!dateString) return 'Not available';
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    };

    return (
        <>
            <Head title="My Profile" />
            <div className="mx-auto max-w-4xl px-4 py-12">
                    {/* Header with Edit Button */}
                    <div className="mb-8 flex items-center justify-between">
                        <div>
                            <h1 className="text-4xl font-bold tracking-tight text-white mb-2">My Profile</h1>
                            <p className="text-slate-400">Your personal information and account details</p>
                        </div>
                        <Link href="/settings/profile">
                            <Button className="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white">
                                <Edit size={18} />
                                Edit Profile
                            </Button>
                        </Link>
                    </div>

                    {/* Main Profile Card */}
                    <div className="rounded-xl border border-slate-700/50 bg-slate-800/30 p-8 backdrop-blur-sm mb-6">
                        {/* Profile Header */}
                        <div className="mb-8 pb-8 border-b border-slate-700/50">
                            <div className="flex items-start gap-6">
                                {/* Avatar */}
                                <div className="relative">
                                    {user.avatar_url ? (
                                        <img
                                            src={user.avatar_url}
                                            alt={user.name}
                                            className="w-24 h-24 rounded-full border-2 border-blue-400 object-cover"
                                        />
                                    ) : (
                                        <div className="w-24 h-24 rounded-full border-2 border-blue-400 bg-gradient-to-br from-blue-400 to-cyan-500 flex items-center justify-center">
                                            <User size={48} className="text-white" />
                                        </div>
                                    )}
                                    {user.is_active && (
                                        <div className="absolute bottom-0 right-0 w-6 h-6 bg-green-500 border-2 border-slate-800 rounded-full flex items-center justify-center">
                                            <CheckCircle size={16} className="text-white" />
                                        </div>
                                    )}
                                </div>

                                {/* User Basic Info */}
                                <div className="flex-1">
                                    <h2 className="text-3xl font-bold text-white mb-2">{user.name}</h2>
                                    <p className="text-slate-400 mb-4">{user.bio || 'No bio added yet'}</p>
                                    <div className="flex flex-wrap gap-3">
                                        {user.is_verified && (
                                            <span className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-green-500/20 text-green-300 text-sm font-medium border border-green-500/30">
                                                <CheckCircle size={14} />
                                                Verified
                                            </span>
                                        )}
                                        {user.is_active && (
                                            <span className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/20 text-blue-300 text-sm font-medium border border-blue-500/30">
                                                <CheckCircle size={14} />
                                                Active
                                            </span>
                                        )}
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Information Grid */}
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {/* Email */}
                            <div className="rounded-lg border border-slate-700/30 bg-slate-900/50 p-4">
                                <div className="flex items-center gap-3 mb-2">
                                    <Mail size={18} className="text-blue-400" />
                                    <span className="text-slate-400 text-sm font-medium">Email Address</span>
                                </div>
                                <p className="text-white text-lg break-all">{user.email}</p>
                            </div>

                            {/* Timezone */}
                            <div className="rounded-lg border border-slate-700/30 bg-slate-900/50 p-4">
                                <div className="flex items-center gap-3 mb-2">
                                    <MapPin size={18} className="text-purple-400" />
                                    <span className="text-slate-400 text-sm font-medium">Timezone</span>
                                </div>
                                <p className="text-white text-lg">{user.timezone || 'Not set'}</p>
                            </div>

                            {/* Email Verified Date */}
                            <div className="rounded-lg border border-slate-700/30 bg-slate-900/50 p-4">
                                <div className="flex items-center gap-3 mb-2">
                                    <CheckCircle size={18} className="text-green-400" />
                                    <span className="text-slate-400 text-sm font-medium">Email Verified</span>
                                </div>
                                <p className="text-white text-lg">
                                    {user.email_verified_at ? formatDate(user.email_verified_at) : 'Not verified'}
                                </p>
                            </div>

                            {/* Last Login */}
                            <div className="rounded-lg border border-slate-700/30 bg-slate-900/50 p-4">
                                <div className="flex items-center gap-3 mb-2">
                                    <Clock size={18} className="text-orange-400" />
                                    <span className="text-slate-400 text-sm font-medium">Last Login</span>
                                </div>
                                <p className="text-white text-lg">
                                    {user.last_login_at ? formatDate(user.last_login_at) : 'No login recorded'}
                                </p>
                            </div>

                            {/* Account Created */}
                            <div className="rounded-lg border border-slate-700/30 bg-slate-900/50 p-4">
                                <div className="flex items-center gap-3 mb-2">
                                    <Calendar size={18} className="text-cyan-400" />
                                    <span className="text-slate-400 text-sm font-medium">Account Created</span>
                                </div>
                                <p className="text-white text-lg">{formatDate(user.created_at)}</p>
                            </div>

                            {/* Last Updated */}
                            <div className="rounded-lg border border-slate-700/30 bg-slate-900/50 p-4">
                                <div className="flex items-center gap-3 mb-2">
                                    <Calendar size={18} className="text-indigo-400" />
                                    <span className="text-slate-400 text-sm font-medium">Last Updated</span>
                                </div>
                                <p className="text-white text-lg">{formatDate(user.updated_at)}</p>
                            </div>

                            {/* User ID */}
                            <div className="rounded-lg border border-slate-700/30 bg-slate-900/50 p-4">
                                <div className="flex items-center gap-3 mb-2">
                                    <User size={18} className="text-slate-400" />
                                    <span className="text-slate-400 text-sm font-medium">User ID</span>
                                </div>
                                <p className="text-white text-lg font-mono">{user.id}</p>
                            </div>
                        </div>
                    </div>

                    {/* Quick Actions */}
                    <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <Link href="/settings/profile" className="block">
                            <div className="rounded-lg border border-slate-700/30 bg-slate-800/30 p-4 hover:bg-slate-800/50 transition cursor-pointer">
                                <h3 className="text-white font-semibold mb-1">Edit Profile</h3>
                                <p className="text-slate-400 text-sm">Update your name and email</p>
                            </div>
                        </Link>

                        <Link href="/plans" className="block">
                            <div className="rounded-lg border border-slate-700/30 bg-slate-800/30 p-4 hover:bg-slate-800/50 transition cursor-pointer">
                                <h3 className="text-white font-semibold mb-1">My Plans</h3>
                                <p className="text-slate-400 text-sm">View and manage your plans</p>
                            </div>
                        </Link>

                        <Link href="/settings/security" className="block">
                            <div className="rounded-lg border border-slate-700/30 bg-slate-800/30 p-4 hover:bg-slate-800/50 transition cursor-pointer">
                                <h3 className="text-white font-semibold mb-1">Security</h3>
                                <p className="text-slate-400 text-sm">Manage your password</p>
                            </div>
                        </Link>

                        <Link href="/settings/appearance" className="block">
                            <div className="rounded-lg border border-slate-700/30 bg-slate-800/30 p-4 hover:bg-slate-800/50 transition cursor-pointer">
                                <h3 className="text-white font-semibold mb-1">Appearance</h3>
                                <p className="text-slate-400 text-sm">Customize your theme</p>
                            </div>
                        </Link>
                    </div>
                </div>
            </>
        );
    }
