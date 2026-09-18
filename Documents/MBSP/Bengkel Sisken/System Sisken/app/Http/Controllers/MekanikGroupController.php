<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MekanikGroupController extends Controller
{
    // Only these mekanik categories are group-based (MAINTENANCE is individual-based).
    private const GROUP_CATEGORIES = ['SERVICE', 'BREAKDOWN', 'WELDING'];

    // MAINTENANCE tasks (Pembaikan Berat / Pembaikan Ringan) are assigned to one mekanik at a time, not a group.
    private const INDIVIDUAL_CATEGORIES = ['MAINTENANCE'];

    public function listIndividual()
    {
        $mekaniks = $this->getActiveIndividualPool();

        // A mekanik can only belong to one group or be an individual mekanik, not both.
        $takenUserIds = array_merge(
            $this->getAllActiveGroupMemberUserIds(),
            $this->getAllActiveIndividualPoolUserIds()
        );

        $availableUsers = DB::connection('oracle')
            ->table('USERS as u')
            ->select(
                DB::raw('u.ID as "ID"'),
                DB::raw('u.NOSTAFF as "NOSTAFF"'),
                DB::raw('u.NAMA as "NAMA"'),
                DB::raw('u.JABATAN as "JABATAN"'),
                DB::raw('u.MODULE_ROLE as "MODULE_ROLE"')
            )
            ->whereRaw("UPPER(u.STATUS_DATA) = 'ACTIVE'")
            ->when(!empty($takenUserIds), function ($query) use ($takenUserIds) {
                $query->whereNotIn('u.ID', $takenUserIds);
            })
            ->orderBy('u.NAMA')
            ->get()
            ->filter(fn ($u) => $this->hasPenyelenggaraanRole50($u))
            ->values();

        $categoryNames = DB::connection('oracle')
            ->table('KATEGORI_PENYELENGGARAAN')
            ->select(DB::raw('NAMA_KATEGORI as "NAMA_KATEGORI"'))
            ->whereIn('MEKANIK_CATEGORY', self::INDIVIDUAL_CATEGORIES)
            ->whereRaw("UPPER(STATUS_DATA) = 'ACTIVE'")
            ->orderBy('NAMA_KATEGORI')
            ->get()
            ->map(fn ($c) => $c->NAMA_KATEGORI ?? $c->nama_kategori ?? null)
            ->filter()
            ->values();

        return view('pages.mekanik.mekanik-individual-list', [
            'title' => 'Mekanik Individu (Maintenance)',
            'mekaniks' => $mekaniks,
            'availableUsers' => $availableUsers,
            'categoryNames' => $categoryNames,
        ]);
    }

    public function storeIndividual(Request $request)
    {
        $request->validate([
            'user_id' => 'required|array|min:1',
            'user_id.*' => 'required',
        ], [
            'user_id.required' => 'Sila pilih sekurang-kurangnya seorang mekanik.',
            'user_id.array' => 'Format pilihan mekanik tidak sah.',
            'user_id.min' => 'Sila pilih sekurang-kurangnya seorang mekanik.',
        ]);

        $userIds = collect($request->user_id)
            ->filter()
            ->unique()
            ->values();

        if ($userIds->isEmpty()) {
            return back()
                ->withErrors(['user_id' => 'Sila pilih sekurang-kurangnya seorang mekanik.'])
                ->withInput();
        }

        // A mekanik can only belong to one group or be an individual mekanik, not both.
        $alreadyTaken = DB::connection('oracle')
            ->table('MEKANIK_GROUP_MEMBER')
            ->whereIn('USER_ID', $userIds->toArray())
            ->whereRaw("UPPER(STATUS_DATA) = 'ACTIVE'")
            ->exists()
            || DB::connection('oracle')
                ->table('MEKANIK_INDIVIDUAL_POOL')
                ->whereIn('USER_ID', $userIds->toArray())
                ->whereRaw("UPPER(STATUS_DATA) = 'ACTIVE'")
                ->exists();

        if ($alreadyTaken) {
            return back()
                ->withErrors(['user_id' => 'Terdapat pengguna yang telah menjadi mekanik kumpulan atau individu sedia ada.'])
                ->withInput();
        }

        $now = now('Asia/Kuala_Lumpur');
        $createdBy = session('nostaff') ?? 'SYSTEM';

        $insertData = $userIds->map(fn ($userId) => [
            'CATEGORY_CODE' => self::INDIVIDUAL_CATEGORIES[0],
            'USER_ID' => $userId,
            'AUTO_ASSIGN_ENABLED' => 'Y',
            'STATUS_DATA' => 'ACTIVE',
            'CREATED_BY' => $createdBy,
            'CREATED_AT' => $now,
        ])->toArray();

        DB::connection('oracle')
            ->table('MEKANIK_INDIVIDUAL_POOL')
            ->insert($insertData);

        return redirect()
            ->route('mekanik-individual-list')
            ->with('success', 'Mekanik individu berjaya ditambah.');
    }

    public function removeIndividual($id)
    {
        $affected = DB::connection('oracle')
            ->table('MEKANIK_INDIVIDUAL_POOL')
            ->where('ID', $id)
            ->whereRaw("UPPER(STATUS_DATA) = 'ACTIVE'")
            ->update([
                'STATUS_DATA' => 'DELETE',
                'UPDATED_BY' => session('nostaff') ?? 'SYSTEM',
                'UPDATED_AT' => now('Asia/Kuala_Lumpur'),
            ]);

        if ($affected === 0) {
            return back()->with('error', 'Mekanik individu tidak dijumpai atau telah dikeluarkan.');
        }

        return back()->with('success', 'Mekanik individu berjaya dikeluarkan.');
    }

    public function list()
    {
        $groups = DB::connection('oracle')
            ->table('MEKANIK_GROUP as g')
            ->select(
                DB::raw('g.ID as "ID"'),
                DB::raw('g.CATEGORY_CODE as "CATEGORY_CODE"'),
                DB::raw('g.GROUP_CODE as "GROUP_CODE"'),
                DB::raw('g.GROUP_NAME as "GROUP_NAME"'),
                DB::raw('g.MAX_MEMBER as "MAX_MEMBER"')
            )
            ->whereRaw("UPPER(g.STATUS_DATA) = 'ACTIVE'")
            ->orderBy('g.CATEGORY_CODE')
            ->orderBy('g.GROUP_NAME')
            ->get();

        $groupIds = $groups
            ->map(fn ($g) => $g->ID ?? $g->id ?? null)
            ->filter()
            ->values()
            ->toArray();

        $membersByGroup = collect();

        if (!empty($groupIds)) {
            $membersByGroup = DB::connection('oracle')
                ->table('MEKANIK_GROUP_MEMBER as m')
                ->leftJoin('USERS as u', 'u.ID', '=', 'm.USER_ID')
                ->select(
                    DB::raw('m.GROUP_ID as "GROUP_ID"'),
                    DB::raw('m.USER_ID as "USER_ID"'),
                    DB::raw('m.IS_LEADER as "IS_LEADER"'),
                    DB::raw('u.NAMA as "NAMA"')
                )
                ->whereIn('m.GROUP_ID', $groupIds)
                ->whereRaw("UPPER(m.STATUS_DATA) = 'ACTIVE'")
                ->get()
                ->groupBy(fn ($row) => $row->GROUP_ID ?? $row->group_id ?? null);
        }

        $categories = $this->getCategoryNameMap();

        $groups = $groups->map(function ($g) use ($membersByGroup, $categories) {
            $id = $g->ID ?? $g->id ?? null;
            $categoryCode = $g->CATEGORY_CODE ?? $g->category_code ?? null;
            $groupMembers = $membersByGroup->get($id, collect());

            $leader = $groupMembers->first(function ($m) {
                return strtoupper(trim($m->IS_LEADER ?? $m->is_leader ?? '')) === 'Y';
            });

            return (object) [
                'ID' => $id,
                'CATEGORY_CODE' => $categoryCode,
                'CATEGORY_NAME' => $categories[$categoryCode] ?? $categoryCode,
                'GROUP_CODE' => $g->GROUP_CODE ?? $g->group_code ?? '-',
                'GROUP_NAME' => $g->GROUP_NAME ?? $g->group_name ?? '-',
                'MAX_MEMBER' => $g->MAX_MEMBER ?? $g->max_member ?? 0,
                'MEMBER_COUNT' => $groupMembers->count(),
                'LEADER_NAME' => $leader->NAMA ?? $leader->nama ?? '-',
                'STATUS_DATA' => 'ACTIVE',
            ];
        })->values();

        return view('pages.mekanik.mekanik-group-list', [
            'title' => 'Senarai Kumpulan Mekanik',
            'groups' => $groups,
        ]);
    }

    public function create()
    {
        return view('pages.mekanik.mekanik-group-add', [
            'title' => 'Tambah Kumpulan Mekanik',
            'categories' => $this->getGroupCategories(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_code' => 'required|in:' . implode(',', self::GROUP_CATEGORIES),
            'group_code' => 'required|string|max:50',
            'group_name' => 'required|string|max:150',
            'max_member' => 'required|integer|min:1',
        ], [
            'category_code.required' => 'Sila pilih kategori mekanik.',
            'category_code.in' => 'Kategori mekanik tidak sah.',
            'group_code.required' => 'Sila masukkan kod kumpulan.',
            'group_name.required' => 'Sila masukkan nama kumpulan.',
            'max_member.required' => 'Sila masukkan bilangan maksimum ahli.',
            'max_member.integer' => 'Bilangan maksimum ahli mestilah nombor.',
            'max_member.min' => 'Bilangan maksimum ahli mestilah sekurang-kurangnya 1.',
        ]);

        $duplicate = DB::connection('oracle')
            ->table('MEKANIK_GROUP')
            ->whereRaw('UPPER(GROUP_CODE) = UPPER(?)', [$request->group_code])
            ->whereRaw("UPPER(STATUS_DATA) = 'ACTIVE'")
            ->exists();

        if ($duplicate) {
            return back()
                ->withErrors(['group_code' => 'Kod kumpulan ini telah digunakan.'])
                ->withInput();
        }

        DB::connection('oracle')
            ->table('MEKANIK_GROUP')
            ->insert([
                'CATEGORY_CODE' => $request->category_code,
                'GROUP_CODE' => $request->group_code,
                'GROUP_NAME' => $request->group_name,
                'MAX_MEMBER' => $request->max_member,
                'STATUS_DATA' => 'ACTIVE',
                'CREATED_BY' => session('nostaff') ?? 'SYSTEM',
                'CREATED_AT' => now('Asia/Kuala_Lumpur'),
            ]);

        return redirect()
            ->route('mekanik-group-list')
            ->with('success', 'Kumpulan mekanik berjaya ditambah.');
    }

    public function edit($id)
    {
        $group = $this->findActiveGroup($id);

        if (!$group) {
            return redirect()
                ->route('mekanik-group-list')
                ->with('error', 'Kumpulan mekanik tidak dijumpai.');
        }

        $members = $this->getActiveMembers($id);

        // A mekanik can only belong to one group or be an individual mekanik, so
        // exclude anyone already active in ANY group or the individual pool.
        $takenUserIds = array_merge(
            $this->getAllActiveGroupMemberUserIds(),
            $this->getAllActiveIndividualPoolUserIds()
        );

        $availableUsers = DB::connection('oracle')
            ->table('USERS as u')
            ->select(
                DB::raw('u.ID as "ID"'),
                DB::raw('u.NOSTAFF as "NOSTAFF"'),
                DB::raw('u.NAMA as "NAMA"'),
                DB::raw('u.JABATAN as "JABATAN"'),
                DB::raw('u.MODULE_ROLE as "MODULE_ROLE"')
            )
            ->whereRaw("UPPER(u.STATUS_DATA) = 'ACTIVE'")
            ->when(!empty($takenUserIds), function ($query) use ($takenUserIds) {
                $query->whereNotIn('u.ID', $takenUserIds);
            })
            ->orderBy('u.NAMA')
            ->get()
            ->filter(fn ($u) => $this->hasPenyelenggaraanRole50($u))
            ->values();

        $categories = $this->getCategoryNameMap();
        $categoryCode = $group->CATEGORY_CODE ?? $group->category_code ?? null;

        return view('pages.mekanik.mekanik-group-edit', [
            'title' => 'Uruskan Kumpulan Mekanik',
            'group' => (object) [
                'ID' => $group->ID ?? $group->id,
                'CATEGORY_CODE' => $categoryCode,
                'CATEGORY_NAME' => $categories[$categoryCode] ?? $categoryCode,
                'GROUP_CODE' => $group->GROUP_CODE ?? $group->group_code,
                'GROUP_NAME' => $group->GROUP_NAME ?? $group->group_name,
                'MAX_MEMBER' => $group->MAX_MEMBER ?? $group->max_member,
            ],
            'members' => $members,
            'availableUsers' => $availableUsers,
        ]);
    }

    public function update(Request $request, $id)
    {
        $group = $this->findActiveGroup($id);

        if (!$group) {
            return redirect()
                ->route('mekanik-group-list')
                ->with('error', 'Kumpulan mekanik tidak dijumpai.');
        }

        $request->validate([
            'group_name' => 'required|string|max:150',
            'max_member' => 'required|integer|min:1',
        ], [
            'group_name.required' => 'Sila masukkan nama kumpulan.',
            'max_member.required' => 'Sila masukkan bilangan maksimum ahli.',
            'max_member.integer' => 'Bilangan maksimum ahli mestilah nombor.',
            'max_member.min' => 'Bilangan maksimum ahli mestilah sekurang-kurangnya 1.',
        ]);

        $activeMemberCount = $this->getActiveMembers($id)->count();

        if ($request->max_member < $activeMemberCount) {
            return back()
                ->withErrors([
                    'max_member' => "Bilangan maksimum ahli tidak boleh kurang daripada bilangan ahli semasa ({$activeMemberCount}).",
                ])
                ->withInput();
        }

        DB::connection('oracle')
            ->table('MEKANIK_GROUP')
            ->where('ID', $id)
            ->update([
                'GROUP_NAME' => $request->group_name,
                'MAX_MEMBER' => $request->max_member,
                'UPDATED_BY' => session('nostaff') ?? 'SYSTEM',
                'UPDATED_AT' => now('Asia/Kuala_Lumpur'),
            ]);

        return redirect()
            ->route('mekanik-group-edit', $id)
            ->with('success', 'Maklumat kumpulan mekanik berjaya dikemaskini.');
    }

    public function addMember(Request $request, $id)
    {
        $group = $this->findActiveGroup($id);

        if (!$group) {
            return redirect()
                ->route('mekanik-group-list')
                ->with('error', 'Kumpulan mekanik tidak dijumpai.');
        }

        $request->validate([
            'user_id' => 'required|array|min:1',
            'user_id.*' => 'required',
        ], [
            'user_id.required' => 'Sila pilih sekurang-kurangnya seorang ahli.',
            'user_id.array' => 'Format pilihan ahli tidak sah.',
            'user_id.min' => 'Sila pilih sekurang-kurangnya seorang ahli.',
        ]);

        $userIds = collect($request->user_id)
            ->filter()
            ->unique()
            ->values();

        if ($userIds->isEmpty()) {
            return back()
                ->withErrors(['user_id' => 'Sila pilih sekurang-kurangnya seorang ahli.'])
                ->withInput();
        }

        $maxMember = (int) ($group->MAX_MEMBER ?? $group->max_member ?? 0);
        $currentActiveCount = $this->getActiveMembers($id)->count();

        if (($currentActiveCount + $userIds->count()) > $maxMember) {
            $slotsLeft = max($maxMember - $currentActiveCount, 0);

            return back()
                ->withErrors([
                    'user_id' => "Kumpulan ini hanya boleh mempunyai maksimum {$maxMember} ahli. Baki tempat kosong: {$slotsLeft}.",
                ])
                ->withInput();
        }

        // A mekanik can only belong to one group or be an individual mekanik, so
        // block re-adding anyone already active in another group or the individual pool.
        $alreadyActive = DB::connection('oracle')
            ->table('MEKANIK_GROUP_MEMBER')
            ->whereIn('USER_ID', $userIds->toArray())
            ->whereRaw("UPPER(STATUS_DATA) = 'ACTIVE'")
            ->exists()
            || DB::connection('oracle')
                ->table('MEKANIK_INDIVIDUAL_POOL')
                ->whereIn('USER_ID', $userIds->toArray())
                ->whereRaw("UPPER(STATUS_DATA) = 'ACTIVE'")
                ->exists();

        if ($alreadyActive) {
            return back()
                ->withErrors(['user_id' => 'Terdapat pengguna yang telah menjadi ahli aktif kumpulan lain atau mekanik individu.'])
                ->withInput();
        }

        $now = now('Asia/Kuala_Lumpur');
        $createdBy = session('nostaff') ?? 'SYSTEM';

        $hasLeader = DB::connection('oracle')
            ->table('MEKANIK_GROUP_MEMBER')
            ->where('GROUP_ID', $id)
            ->whereRaw("UPPER(STATUS_DATA) = 'ACTIVE'")
            ->whereRaw("UPPER(IS_LEADER) = 'Y'")
            ->exists();

        $insertData = [];

        foreach ($userIds as $index => $userId) {
            $insertData[] = [
                'GROUP_ID' => $id,
                'USER_ID' => $userId,
                'IS_LEADER' => (!$hasLeader && $index === 0) ? 'Y' : 'N',
                'JOINED_AT' => $now,
                'STATUS_DATA' => 'ACTIVE',
                'CREATED_BY' => $createdBy,
                'CREATED_AT' => $now,
            ];
        }

        DB::connection('oracle')
            ->table('MEKANIK_GROUP_MEMBER')
            ->insert($insertData);

        return redirect()
            ->route('mekanik-group-edit', $id)
            ->with('success', 'Ahli berjaya ditambah ke dalam kumpulan.');
    }

    public function removeMember($groupId, $memberId)
    {
        $affected = DB::connection('oracle')
            ->table('MEKANIK_GROUP_MEMBER')
            ->where('ID', $memberId)
            ->where('GROUP_ID', $groupId)
            ->whereRaw("UPPER(STATUS_DATA) = 'ACTIVE'")
            ->update([
                'STATUS_DATA' => 'DELETE',
                'LEFT_AT' => now('Asia/Kuala_Lumpur'),
                'UPDATED_BY' => session('nostaff') ?? 'SYSTEM',
                'UPDATED_AT' => now('Asia/Kuala_Lumpur'),
            ]);

        if ($affected === 0) {
            return back()->with('error', 'Ahli tidak dijumpai atau telah dikeluarkan.');
        }

        return back()->with('success', 'Ahli berjaya dikeluarkan daripada kumpulan.');
    }

    public function setLeader($groupId, $memberId)
    {
        $member = DB::connection('oracle')
            ->table('MEKANIK_GROUP_MEMBER')
            ->where('ID', $memberId)
            ->where('GROUP_ID', $groupId)
            ->whereRaw("UPPER(STATUS_DATA) = 'ACTIVE'")
            ->first();

        if (!$member) {
            return back()->with('error', 'Ahli tidak dijumpai atau tidak aktif.');
        }

        DB::connection('oracle')->transaction(function () use ($groupId, $memberId) {
            DB::connection('oracle')
                ->table('MEKANIK_GROUP_MEMBER')
                ->where('GROUP_ID', $groupId)
                ->whereRaw("UPPER(STATUS_DATA) = 'ACTIVE'")
                ->update([
                    'IS_LEADER' => 'N',
                    'UPDATED_BY' => session('nostaff') ?? 'SYSTEM',
                    'UPDATED_AT' => now('Asia/Kuala_Lumpur'),
                ]);

            DB::connection('oracle')
                ->table('MEKANIK_GROUP_MEMBER')
                ->where('ID', $memberId)
                ->update([
                    'IS_LEADER' => 'Y',
                    'UPDATED_BY' => session('nostaff') ?? 'SYSTEM',
                    'UPDATED_AT' => now('Asia/Kuala_Lumpur'),
                ]);
        });

        return back()->with('success', 'Ketua kumpulan berjaya dikemaskini.');
    }

    public function delete($id)
    {
        $affected = DB::connection('oracle')
            ->table('MEKANIK_GROUP')
            ->where('ID', $id)
            ->whereRaw("UPPER(STATUS_DATA) = 'ACTIVE'")
            ->update([
                'STATUS_DATA' => 'DELETE',
                'UPDATED_BY' => session('nostaff') ?? 'SYSTEM',
                'UPDATED_AT' => now('Asia/Kuala_Lumpur'),
            ]);

        if ($affected === 0) {
            return back()->with('error', 'Kumpulan mekanik tidak dijumpai.');
        }

        return back()->with('success', 'Kumpulan mekanik berjaya dipadam.');
    }

    private function findActiveGroup($id)
    {
        return DB::connection('oracle')
            ->table('MEKANIK_GROUP')
            ->where('ID', $id)
            ->whereRaw("UPPER(STATUS_DATA) = 'ACTIVE'")
            ->first();
    }

    private function getAllActiveGroupMemberUserIds()
    {
        return DB::connection('oracle')
            ->table('MEKANIK_GROUP_MEMBER')
            ->select(DB::raw('USER_ID as "USER_ID"'))
            ->whereRaw("UPPER(STATUS_DATA) = 'ACTIVE'")
            ->get()
            ->map(fn ($m) => $m->USER_ID ?? $m->user_id ?? null)
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }

    private function getAllActiveIndividualPoolUserIds()
    {
        return DB::connection('oracle')
            ->table('MEKANIK_INDIVIDUAL_POOL')
            ->select(DB::raw('USER_ID as "USER_ID"'))
            ->whereRaw("UPPER(STATUS_DATA) = 'ACTIVE'")
            ->get()
            ->map(fn ($m) => $m->USER_ID ?? $m->user_id ?? null)
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }

    private function getActiveIndividualPool()
    {
        return DB::connection('oracle')
            ->table('MEKANIK_INDIVIDUAL_POOL as pool')
            ->leftJoin('USERS as u', 'u.ID', '=', 'pool.USER_ID')
            ->select(
                DB::raw('pool.ID as "ID"'),
                DB::raw('pool.USER_ID as "USER_ID"'),
                DB::raw('pool.AUTO_ASSIGN_ENABLED as "AUTO_ASSIGN_ENABLED"'),
                DB::raw('u.NOSTAFF as "NOSTAFF"'),
                DB::raw('u.NAMA as "NAMA"'),
                DB::raw('u.JABATAN as "JABATAN"')
            )
            ->whereRaw("UPPER(pool.STATUS_DATA) = 'ACTIVE'")
            ->orderBy('u.NAMA')
            ->get();
    }

    private function getActiveMembers($groupId)
    {
        return DB::connection('oracle')
            ->table('MEKANIK_GROUP_MEMBER as m')
            ->leftJoin('USERS as u', 'u.ID', '=', 'm.USER_ID')
            ->select(
                DB::raw('m.ID as "ID"'),
                DB::raw('m.GROUP_ID as "GROUP_ID"'),
                DB::raw('m.USER_ID as "USER_ID"'),
                DB::raw('m.IS_LEADER as "IS_LEADER"'),
                DB::raw('m.JOINED_AT as "JOINED_AT"'),
                DB::raw('u.NOSTAFF as "NOSTAFF"'),
                DB::raw('u.NAMA as "NAMA"'),
                DB::raw('u.JABATAN as "JABATAN"')
            )
            ->where('m.GROUP_ID', $groupId)
            ->whereRaw("UPPER(m.STATUS_DATA) = 'ACTIVE'")
            ->orderByDesc(DB::raw("UPPER(m.IS_LEADER)"))
            ->orderBy('u.NAMA')
            ->get();
    }

    private function hasPenyelenggaraanRole50($user)
    {
        $moduleRoleRaw = $user->MODULE_ROLE ?? $user->module_role ?? '[]';
        $moduleRoles = json_decode($moduleRoleRaw, true);

        if (!is_array($moduleRoles)) {
            return false;
        }

        foreach ($moduleRoles as $item) {
            $module = strtoupper(trim($item['module'] ?? ''));
            $role = (int) ($item['role'] ?? 0);

            if ($module === 'PENYELENGGARAAN' && $role === 50) {
                return true;
            }
        }

        return false;
    }

    private function getGroupCategories()
    {
        return DB::connection('oracle')
            ->table('KATEGORI_PENYELENGGARAAN')
            ->select(
                DB::raw('MEKANIK_CATEGORY as "MEKANIK_CATEGORY"'),
                DB::raw('NAMA_KATEGORI as "NAMA_KATEGORI"')
            )
            ->whereIn('MEKANIK_CATEGORY', self::GROUP_CATEGORIES)
            ->whereRaw("UPPER(STATUS_DATA) = 'ACTIVE'")
            ->orderBy('MEKANIK_CATEGORY')
            ->get()
            ->unique(fn ($r) => $r->MEKANIK_CATEGORY ?? $r->mekanik_category ?? null)
            ->values();
    }

    private function getCategoryNameMap()
    {
        return $this->getGroupCategories()
            ->mapWithKeys(function ($r) {
                $code = $r->MEKANIK_CATEGORY ?? $r->mekanik_category ?? null;
                $name = $r->NAMA_KATEGORI ?? $r->nama_kategori ?? $code;

                return [$code => $name];
            })
            ->toArray();
    }
}
