<template>
  <div class="skeleton-card">
    <div class="skeleton-img"></div>
    <div class="skeleton-line title"></div>
    <div class="skeleton-line date"></div>
    <div class="skeleton-line text"></div>
    <div class="skeleton-line text short"></div>
  </div>
</template>

<style scoped>
.skeleton-card {
  border: 1px solid #eee;
  padding: 15px;
  border-radius: 8px;
  background: #fff;
}

.skeleton-img {
  width: 100%;
  height: 160px;
  border-radius: 6px;
  background: #e5e7eb;
  margin-bottom: 10px;
}

.skeleton-line {
  height: 12px;
  background: #e5e7eb;
  border-radius: 4px;
  margin-bottom: 8px;
  animation: pulse 1.4s infinite ease-in-out;
}

.skeleton-line.title {
  width: 70%;
  height: 16px;
}

.skeleton-line.date {
  width: 40%;
}

.skeleton-line.short {
  width: 60%;
}

@keyframes pulse {
  0% { opacity: 1 }
  50% { opacity: 0.4 }
  100% { opacity: 1 }
}
</style>
