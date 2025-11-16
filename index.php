<?php
require_once 'functions.php';

$errors = [];
$success_message = '';
$edit_contact = null;
$action = 'add';

$is_logged_in = $_SESSION['logged_in'] ?? false;
$username = $_SESSION['username'] ?? 'Guest';

if (isset($_POST['login'])) {
    if ($_POST['username'] === 'admin' && $_POST['password'] === '123') {
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = 'admin';
        $is_logged_in = true;
        $success_message = "Login Berhasil!";
    } else {
        $errors[] = "Username atau password salah. (Hint: admin/123)";
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

if (!$is_logged_in) {
} else {
    if (isset($_GET['delete_id'])) {
        $delete_id = $_GET['delete_id'];
        if (deleteContact($delete_id)) {
            $success_message = "Kontak berhasil dihapus!";
        } else {
            $errors[] = "ID kontak tidak ditemukan.";
        }
        header('Location: index.php');
        exit();
    }

    if (isset($_GET['edit_id'])) {
        $edit_id = $_GET['edit_id'];
        $contact_data = getContactById($edit_id);
        if ($contact_data) {
            $edit_contact = $contact_data;
            $action = 'edit';
        } else {
            $errors[] = "Kontak tidak ditemukan untuk diedit.";
        }
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && (isset($_POST['add_contact']) || isset($_POST['edit_contact_id']))) {
        
        $validation_result = validateContact($_POST);
        $errors = $validation_result['errors'];
        $data = $validation_result['data'];

        if (empty($errors)) {
            if (isset($_POST['edit_contact_id']) && !empty($_POST['edit_contact_id'])) {
                $id_to_edit = $_POST['edit_contact_id'];
                updateContact($id_to_edit, $data);
                $success_message = "Kontak berhasil diperbarui!";
            } else {
                addContact($data);
                $success_message = "Kontak baru berhasil ditambahkan!";
            }
            header('Location: index.php'); 
            exit();

        } else {
            if (isset($_POST['edit_contact_id']) && !empty($_POST['edit_contact_id'])) {
                $edit_contact = array_merge($data, ['id' => $_POST['edit_contact_id']]);
                $action = 'edit';
            }
        }
    }
}

$contacts = getContacts();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Kontak Sederhana (PHP & Tailwind)</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .container {
            max-width: 1024px;
        }
    </style>
</head>
<body class="bg-gray-100 p-8">

    <div class="container mx-auto bg-white shadow-xl rounded-lg p-6">
        <h1 class="text-3xl font-bold text-indigo-700 mb-6 border-b pb-2">
            <span class="mr-2">📝</span>Sistem Manajemen Kontak
        </h1>

        <?php if (!empty($errors)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Error!</strong>
                <ul class="list-disc ml-5 mt-1">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Sukses!</strong>
                <span class="block sm:inline"><?php echo $success_message; ?></span>
            </div>
        <?php endif; ?>

        <div class="mb-6 p-4 bg-indigo-50 rounded-md flex justify-between items-center">
            <p class="text-indigo-800 font-semibold">
                Selamat Datang, <?php echo htmlspecialchars($username); ?>!
            </p>
            <?php if ($is_logged_in): ?>
                <a href="?logout=1" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded transition duration-150">
                    Logout
                </a>
            <?php else: ?>
                <div class="flex items-center space-x-4">
                    <form method="POST" class="flex space-x-2">
                        <input type="text" name="username" placeholder="Username" required class="border p-2 rounded w-32" value="admin">
                        <input type="password" name="password" placeholder="Password" required class="border p-2 rounded w-32" value="123">
                        <input type="submit" name="login" value="Login" class="bg-indigo-600 hover:bg-indigo-800 text-white font-bold py-2 px-4 rounded transition duration-150 cursor-pointer">
                    </form>
                </div>
            <?php endif; ?>
        </div>

        <?php if (!$is_logged_in): ?>
            <div class="p-10 text-center text-gray-500 bg-gray-50 border-2 border-dashed border-gray-300 rounded-lg">
                <p class="text-xl font-medium">Anda harus **Login** untuk mengelola Kontak.</p>
                <p>Silakan gunakan form di atas untuk login.</p>
            </div>
        <?php else: ?>
            <div class="mb-8 border p-6 rounded-lg shadow-sm">
                <h2 class="text-2xl font-semibold text-indigo-600 mb-4">
                    <span class="mr-1">
                        <?php echo ($action === 'edit' ? '✏️ Edit Kontak' : '➕ Tambah Kontak Baru'); ?>
                    </span>
                </h2>
                
                <form method="POST" action="index.php" class="space-y-4">
                    <?php if ($action === 'edit' && $edit_contact): ?>
                        <input type="hidden" name="edit_contact_id" value="<?php echo htmlspecialchars($_GET['edit_id']); ?>">
                    <?php endif; ?>
                    
                    <div>
                        <label for="nama" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                        <input type="text" id="nama" name="nama" required 
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-indigo-500 focus:border-indigo-500"
                               value="<?php echo htmlspecialchars($edit_contact['nama'] ?? ''); ?>">
                    </div>

                    <div>
                        <label for="telepon" class="block text-sm font-medium text-gray-700">Nomor Telepon</label>
                        <input type="text" id="telepon" name="telepon" required 
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-indigo-500 focus:border-indigo-500"
                               value="<?php echo htmlspecialchars($edit_contact['telepon'] ?? ''); ?>">
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" id="email" name="email" required 
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-indigo-500 focus:border-indigo-500"
                               value="<?php echo htmlspecialchars($edit_contact['email'] ?? ''); ?>">
                    </div>
                    
                    <div>
                        <label for="catatan" class="block text-sm font-medium text-gray-700">Catatan (Opsional)</label>
                        <textarea id="catatan" name="catatan" rows="3" 
                                  class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-indigo-500 focus:border-indigo-500"><?php echo htmlspecialchars($edit_contact['catatan'] ?? ''); ?></textarea>
                    </div>

                    <div class="flex space-x-4">
                        <button type="submit" name="<?php echo ($action === 'edit' ? 'update_contact' : 'add_contact'); ?>" 
                                class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-md shadow-md transition duration-150">
                            <?php echo ($action === 'edit' ? 'Simpan Perubahan' : 'Tambah Kontak'); ?>
                        </button>
                        <?php if ($action === 'edit'): ?>
                            <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-md shadow-md transition duration-150 self-center">
                                Batal
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="mb-8">
                <h2 class="text-2xl font-semibold text-indigo-600 mb-4 border-b pb-1">
                    <span class="mr-1">📋</span>Daftar Kontak (Total: <?php echo count($contacts); ?>)
                </h2>

                <?php if (empty($contacts)): ?>
                    <div class="p-8 text-center text-gray-500 bg-gray-50 rounded-lg">
                        Belum ada kontak yang tersimpan.
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto shadow-md rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Telepon</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($contacts as $id => $contact): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($contact['nama']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($contact['telepon']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($contact['email']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="?edit_id=<?php echo $id; ?>" 
                                               class="text-indigo-600 hover:text-indigo-900 mr-4">Edit</a>
                                            <a href="?delete_id=<?php echo $id; ?>" 
                                               onclick="return confirm('Yakin ingin menghapus kontak ini?')"
                                               class="text-red-600 hover:text-red-900">Hapus</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>