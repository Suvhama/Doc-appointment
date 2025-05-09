function toggleSidebar() {
  const sidebar = document.querySelector('.sidebar');
  const main = document.querySelector('.main');
  sidebar.classList.toggle('hidden');
  main.classList.toggle('sidebar-hidden');
}

function toggleDropdown() {
  const menu = document.getElementById('dropdown-menu');
  menu.classList.toggle('active');
  document.addEventListener('click', function(e) {
      const dropdown = document.querySelector('.dropdown');
      if (!dropdown.contains(e.target)) {
          menu.classList.remove('active');
      }
  }, { once: true });
}

function toggleNotifications() {
  document.getElementById('notificationDropdown').classList.toggle('active');
  document.addEventListener('click', function(e) {
      const notificationBell = document.querySelector('.notification-bell');
      if (!notificationBell.contains(e.target)) {
          document.getElementById('notificationDropdown').classList.remove('active');
      }
  }, { once: true });
}

function markAsRead(button) {
  const notification = button.closest('.notification-item');
  notification.classList.remove('unread');
  updateNotificationCount();
}

function clearNotifications() {
  document.querySelectorAll('.notification-item').forEach(n => n.classList.remove('unread'));
  updateNotificationCount();
}

function updateNotificationCount() {
  const unread = document.querySelectorAll('.notification-item.unread').length;
  document.getElementById('notificationCount').textContent = unread;
  document.getElementById('notificationTotal').textContent = unread;
}

function toggleEditProfile() {
  const form = document.getElementById('edit-profile-form');
  form.style.display = form.style.display === 'none' ? 'block' : 'none';
}

document.querySelector('.search-input').addEventListener('keypress', function(e) {
  if (e.key === 'Enter') {
      e.preventDefault();
      this.closest('form').submit();
  }
});

// Add functionality to dropdown links
document.getElementById('profile-link').addEventListener('click', function(e) {
  e.preventDefault();
  window.location.href = '?section=profile';
});

document.getElementById('settings-link').addEventListener('click', function(e) {
  e.preventDefault();
  window.location.href = '?section=settings';
});

document.getElementById('logout-link').addEventListener('click', function(e) {
  e.preventDefault();
  if (confirm('Are you sure you want to logout?')) {
      window.location.href = '/MediSync/logout';
  }
});