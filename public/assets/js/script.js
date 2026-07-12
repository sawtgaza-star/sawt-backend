function getLang() {
  return localStorage.getItem("lang") || "ar";
}

function t(key) {
  const lang = getLang();
  return translations[lang][key] || key;
}

$(document).ready(function () {
  $(".creators-slider").owlCarousel({
    rtl: true,
    loop: true,
    margin: 20,
    nav: true,
    dots: false,
    navText: [
      "<i class='fa fa-chevron-right'></i>",
      "<i class='fa fa-chevron-left'></i>",
    ],
    responsive: {
      0: { items: 1, nav: true },
      768: { items: 2, nav: true },
      1000: { items: 4, nav: true },
    },
  });
});

const mybutton = document.getElementById("backToTop");

window.onscroll = function () {
  if (
    document.body.scrollTop > 300 ||
    document.documentElement.scrollTop > 300
  ) {
    mybutton.style.display = "block";
  } else {
    mybutton.style.display = "none";
  }
};

const cardContainer = document.querySelector(".main-container");

const observerOptions = {
  threshold: 0.5,
};

document.addEventListener("DOMContentLoaded", () => {
  // جلب كل العناصر التي تحمل كلاس counter
  const counters = document.querySelectorAll(".counter");

  counters.forEach((element) => {
    const targetText = element.innerText;
    const targetNumber = parseInt(targetText.replace(/[^0-9]/g, ""));
    const suffix = targetText.replace(/[0-9]/g, "");

    let currentNumber = 0;
    const duration = 2000;
    const stepTime = Math.abs(Math.floor(duration / targetNumber));

    const timer = setInterval(() => {
      currentNumber += 1;
      element.innerText = currentNumber + suffix;

      if (currentNumber >= targetNumber) {
        clearInterval(timer);
      }
    }, stepTime);
  });
});

const observer = new IntersectionObserver((entries) => {
  entries.forEach((entry) => {
    // التحقق من أن الشاشة أصغر من 992px (جوال أو تابلت)
    if (window.innerWidth <= 992) {
      if (entry.isIntersecting) {
        setTimeout(() => {
          entry.target.classList.add("is-flipped");
        }, 1000);
      }
    }
  });
}, observerOptions);

if (cardContainer) observer.observe(cardContainer);

const users = [
  {
    name: "user_1_name",
    location: "user_1_location",
    avatar: "assets/images/يوسف الدوس.png",
    text: "user_1_text",
  },
  {
    name: "user_2_name",
    location: "user_2_location",
    avatar: "assets/images/محمود زعيتر 2.png",
    text: "user_2_text",
  },
  {
    name: "user_3_name",
    location: "user_3_location",
    avatar: "assets/images/يوسف الدوس.png",
    text: "user_3_text",
  },
  {
    name: "user_4_name",
    location: "user_4_location",
    avatar: "assets/images/محمود زعيتر 2.png",
    text: "user_4_text",
  },
  {
    name: "user_5_name",
    location: "user_5_location",
    avatar: "assets/images/يوسف الدوس.png",
    text: "user_5_text",
  },
];

function updateCardContent(index, carouselId) {
  const user = users[index];
  if (!user) return;

  const isMobile = carouselId === "mobileCarousel";
  const cardSelector = isMobile
    ? ".mobile-slider .opinion-card, .mobile-slider .carousel-item.active"
    : ".opinion-card-wrapper .opinion-card";

  // تحديث الصورة الكبيرة
  const scope = isMobile
    ? document.querySelector(".mobile-slider")
    : document.querySelector(".opinion-card-wrapper");
  if (!scope) return;

  const avatar = scope.querySelector(".user-avatar");
  const name = scope.querySelector("strong");
  const location = scope.querySelector(".text-muted.small");
  const text = scope.querySelector(".opinion-text");

  if (avatar) avatar.src = t(user.avatar);
  if (name) name.textContent = t(user.name);
  if (location) location.textContent = t(user.location);
  if (text) text.textContent = t(user.text);
}

function goToSlide(carouselId, type, index) {
  const el = document.getElementById(carouselId);
  const carousel = bootstrap.Carousel.getOrCreateInstance(el);
  carousel.to(index);

  const selector =
    type === "desktop" ? ".avatar-indicator" : ".avatar-indicator-mobile";
  document.querySelectorAll(selector).forEach((img, i) => {
    img.classList.toggle("active", i === index);
  });

  updateCardContent(index, carouselId);
}

document.addEventListener("DOMContentLoaded", () => {
  // تحميل أول مستخدم ابتداءً
  updateCardContent(0, "opinionsCarousel");
  updateCardContent(0, "mobileCarousel");

  ["opinionsCarousel", "mobileCarousel"].forEach((id) => {
    const el = document.getElementById(id);
    if (!el) return;
    const type = id === "opinionsCarousel" ? "desktop" : "mobile";
    const selector =
      type === "desktop" ? ".avatar-indicator" : ".avatar-indicator-mobile";

    el.addEventListener("slid.bs.carousel", (e) => {
      document.querySelectorAll(selector).forEach((img, i) => {
        img.classList.toggle("active", i === e.to);
      });
      updateCardContent(e.to, id);
    });
  });
});

// ====== البيانات ======

let commentsData = [
  {
    id: 1,
    av: "av-green",
    letter: "ر",
    name: "رنا الصالح",
    text: "قصة ملهمة رغم كل التحديات 💚",
    time: "منذ ساعة",
    likes: 13,
    liked: false,
    replies: [
      {
        av: "av-orange",
        letter: "م",
        name: "مها العبد",
        text: "فعلاً، كلامك صح 🌷",
      },
    ],
  },
  {
    id: 2,
    av: "av-orange",
    letter: "م",
    name: "مها العبد",
    text: "إصرار بيستحق الاحترام 👏",
    time: "منذ ساعتين",
    likes: 13,
    liked: true,
    replies: [],
  },
  {
    id: 3,
    av: "av-blue",
    letter: "أ",
    name: "أحمد باسم",
    text: "حكاية بتعطي دافع للاستمرار",
    time: "22 فبراير",
    likes: 5,
    liked: false,
    replies: [],
  },
];

const extraComments = [
  {
    id: 4,
    av: "av-gray",
    letter: "ي",
    name: "يوسف خالد",
    text: "ما توقعت أشوف قصة بهالمستوى",
    time: "3 مارس",
    likes: 4,
    liked: false,
    replies: [],
  },
  {
    id: 5,
    av: "av-green",
    letter: "ن",
    name: "نور حسن",
    text: "شكراً على هالمحتوى الرائع 🌿",
    time: "5 مارس",
    likes: 7,
    liked: false,
    replies: [],
  },
  {
    id: 6,
    av: "av-orange",
    letter: "ر",
    name: "رامي سمير",
    text: "بتمنى أشوف المزيد من هيك قصص",
    time: "8 مارس",
    likes: 2,
    liked: false,
    replies: [],
  },
];

let commentIdSeq = 7;
let showing = false;
let currentOrder = "newest";

function findComment(id) {
  return (
    commentsData.find((c) => c.id === id) ||
    extraComments.find((c) => c.id === id)
  );
}

function replyMarkup(r) {
  const styleAttr = r.style ? ` style="${r.style}"` : "";
  return `
    <div class="rv-reply">
      <div class="avatar-circle ${r.av} rv-avatar-sm"${styleAttr}>${r.letter}</div>
      <div class="rv-reply-body">
        <span class="rv-name">${r.name || ""}</span>
        <p class="rv-text">${r.text}</p>
      </div>
    </div>`;
}

function repliesMarkup(replies) {
  if (!replies || !replies.length) return "";
  return replies.map(replyMarkup).join("");
}

function commentMarkup(c) {
  const styleAttr = c.style ? ` style="${c.style}"` : "";
  const likes = c.likes != null ? c.likes : 0;
  return `
    <div class="rv-comment-head">
      <div class="rv-user">
        <div class="avatar-circle ${c.av}"${styleAttr}>${c.letter}</div>
        <span class="rv-name">${c.name || ""}</span>
      </div>
      <span class="rv-time">${c.time || ""}</span>
    </div>
    <p class="rv-text">${c.text}</p>
    <div class="rv-actions">
      <div class="rv-like ${c.liked ? "liked" : ""}" onclick="toggleCommentLike(this)">
        <i class="fa-${c.liked ? "solid" : "regular"} fa-heart"></i>
        <span>${likes}</span>
      </div>
      <button type="button" class="rv-reply-btn" onclick="toggleReplyBox(this)">
        <i class="fa-solid fa-reply"></i>
        <span>${t("reply_label")}</span>
      </button>
    </div>
    <div class="rv-replies">${repliesMarkup(c.replies)}</div>
    <div class="rv-reply-box">
      <input
        type="text"
        class="rv-reply-input"
        placeholder="${t("reply_placeholder")}"
        onkeydown="replyKeydown(event, this)"
      />
      <button type="button" class="rv-reply-send" onclick="addReply(this)">
        <i class="fas fa-paper-plane" style="transform: scaleX(-1)"></i>
      </button>
    </div>`;
}

function toggleCommentLike(el) {
  const icon = el.querySelector("i");
  const count = el.querySelector("span");
  const liked = el.classList.toggle("liked");
  icon.classList.toggle("fa-solid", liked);
  icon.classList.toggle("fa-regular", !liked);
  count.textContent = parseInt(count.textContent || "0") + (liked ? 1 : -1);
}

function toggleReplyBox(btn) {
  const box = btn.closest(".rv-comment").querySelector(".rv-reply-box");
  const open = box.classList.toggle("open");
  if (open) {
    const input = box.querySelector(".rv-reply-input");
    setTimeout(() => input.focus(), 50);
  }
}

function replyKeydown(e, input) {
  if (e.key === "Enter") {
    e.preventDefault();
    addReply(input);
  }
}

function addReply(el) {
  const commentEl = el.closest(".rv-comment");
  const box = commentEl.querySelector(".rv-reply-box");
  const input = box.querySelector(".rv-reply-input");
  const text = input.value.trim();
  if (!text) return;

  const colors = ["av-green", "av-orange", "av-blue", "av-gray"];
  const reply = {
    av: colors[Math.floor(Math.random() * colors.length)],
    letter: text[0],
    name: t("you_label"),
    text,
  };

  const comment = findComment(parseInt(commentEl.dataset.id));
  if (comment) {
    comment.replies = comment.replies || [];
    comment.replies.push(reply);
  }

  const repliesEl = commentEl.querySelector(".rv-replies");
  const d = document.createElement("div");
  d.innerHTML = replyMarkup(reply).trim();
  const node = d.firstChild;
  node.style.opacity = "0";
  node.style.transition = "opacity 0.3s";
  repliesEl.appendChild(node);
  requestAnimationFrame(() =>
    requestAnimationFrame(() => (node.style.opacity = "1")),
  );

  input.value = "";
  box.classList.remove("open");
}

function renderComments() {
  const list = document.getElementById("commentsList");

  const sorted =
    currentOrder === "newest" ? [...commentsData].reverse() : [...commentsData];

  list.style.opacity = "0";
  list.style.transition = "opacity 0.2s";

  setTimeout(() => {
    list
      .querySelectorAll(".rv-comment:not(.extra-comment)")
      .forEach((el) => el.remove());

    sorted.forEach((c) => {
      const d = document.createElement("div");
      d.className = "rv-comment";
      d.dataset.id = c.id;
      d.innerHTML = commentMarkup(c);
      list.appendChild(d);
    });

    list.style.opacity = "1";
    list.scrollTop = currentOrder === "newest" ? 0 : list.scrollHeight;
  }, 200);
}

function setTab(el, type) {
  document
    .querySelectorAll(".tab")
    .forEach((t) => t.classList.remove("active"));
  el.classList.add("active");
  currentOrder = type === "الأحدث" ? "newest" : "oldest";
  renderComments();
}

function showLess() {
  const list = document.getElementById("commentsList");
  const btn = document.querySelector(".show-more");

  list.querySelectorAll(".extra-comment").forEach((el) => {
    el.style.opacity = "0";
    setTimeout(() => el.remove(), 300);
  });

  setTimeout(() => {
    list.scrollTop = 0;
  }, 300);

  btn.textContent = t("show_more");
  btn.style.color = "#e1723b";
}

function addComment() {
  const input = document.getElementById("newComment");
  const text = input.value.trim();
  if (!text) return;

  const colors = ["av-green", "av-orange", "av-blue", "av-gray"];
  const randomColor = colors[Math.floor(Math.random() * colors.length)];
  const firstLetter = text[0];

  // أضفه للـ data
  const newC = {
    id: commentIdSeq++,
    av: randomColor,
    letter: firstLetter,
    name: t("you_label"),
    text,
    time: t("now_label"),
    likes: 0,
    liked: false,
    replies: [],
  };
  commentsData.push(newC);

  // ارسمه مباشرة
  const list = document.getElementById("commentsList");
  const d = document.createElement("div");
  d.className = "rv-comment";
  d.dataset.id = newC.id;
  d.style.opacity = "0";
  d.style.transform = "translateY(10px)";
  d.style.transition = "opacity 0.3s, transform 0.3s";
  d.innerHTML = commentMarkup(newC);

  if (currentOrder === "newest") {
    list.insertBefore(d, list.firstChild);
    setTimeout(() => {
      list.scrollTop = 0;
    }, 50);
  } else {
    list.appendChild(d);
    setTimeout(() => {
      list.scrollTop = list.scrollHeight;
    }, 50);
  }

  requestAnimationFrame(() => {
    requestAnimationFrame(() => {
      d.style.opacity = "1";
      d.style.transform = "translateY(0)";
    });
  });

  // حدّث العداد
  const counter = document.querySelector(".comments-count");
  const num = parseInt(counter.textContent.match(/\d+/)[0]) + 1;
  counter.textContent = `${t("comments_word")} (${num})`;

  input.value = "";
  input.style.height = "auto";
}

document.addEventListener("DOMContentLoaded", () => {
  const newComment = document.getElementById("newComment");
  if (!newComment) return;

  renderComments();

  newComment.addEventListener("keydown", (e) => {
    if (e.key === "Enter" && !e.shiftKey) {
      e.preventDefault();
      addComment();
    }
  });

  newComment.addEventListener("input", function () {
    this.style.height = "auto";
    this.style.height = this.scrollHeight + "px";
  });
});

function togglePlay(element) {
  // الحصول على عنصر الفيديو (الـ reel-item الذي يحتوي على الـ play-overlay)
  const reelItem = element.closest(".reel-item");
  const video = reelItem.querySelector("video");
  const playOverlay = element;

  if (video.paused) {
    // تشغيل الفيديو
    video.play();
    // إخفاء أيقونة التشغيل
    playOverlay.style.opacity = "0";
    playOverlay.style.pointerEvents = "none";
    // إضافة كلاس playing
    reelItem.classList.add("playing");
  } else {
    // إيقاف الفيديو
    video.pause();
    // إظهار أيقونة التشغيل
    playOverlay.style.opacity = "1";
    playOverlay.style.pointerEvents = "all";
    // إزالة كلاس playing
    reelItem.classList.remove("playing");
  }
}

// دالة جديدة للتعامل مع الضغط على الفيديو مباشرة
function toggleVideoPlay(videoElement) {
  const reelItem = videoElement.closest(".reel-item");
  const playOverlay = reelItem.querySelector(".play-overlay");

  if (videoElement.paused) {
    // تشغيل الفيديو
    videoElement.play();
    if (playOverlay) {
      playOverlay.style.opacity = "0";
      playOverlay.style.pointerEvents = "none";
    }
    reelItem.classList.add("playing");
  } else {
    // إيقاف الفيديو
    videoElement.pause();
    if (playOverlay) {
      playOverlay.style.opacity = "1";
      playOverlay.style.pointerEvents = "all";
    }
    reelItem.classList.remove("playing");
  }
}

// دوال مساعدة للأزرار الأخرى
function toggleLike(button) {
  const icon = button.querySelector("i");
  if (icon.classList.contains("fa-regular")) {
    icon.classList.remove("fa-regular");
    icon.classList.add("fa-solid");
    icon.style.color = "red";
  } else {
    icon.classList.remove("fa-solid");
    icon.classList.add("fa-regular");
    icon.style.color = "";
  }
}

function toggleSave(button) {
  const icon = button.querySelector("i");
  if (icon.classList.contains("fa-regular")) {
    icon.classList.remove("fa-regular");
    icon.classList.add("fa-solid");
  } else {
    icon.classList.remove("fa-solid");
    icon.classList.add("fa-regular");
  }
}

function shareVideo() {
  alert("مشاركة الفيديو");
}

// ===== تقديم / ترجيع الريلز =====
function skipReel(btn, delta) {
  const video = btn.closest(".reel-item").querySelector("video");
  if (!video || !video.duration) return;
  video.currentTime = Math.min(
    Math.max(video.currentTime + delta, 0),
    video.duration,
  );
}

function formatTime(seconds) {
  if (!seconds || isNaN(seconds)) seconds = 0;
  const m = Math.floor(seconds / 60);
  const s = Math.floor(seconds % 60);
  return m + ":" + (s < 10 ? "0" + s : s);
}

function initReelSeek() {
  document.querySelectorAll(".reel-item").forEach((item) => {
    const video = item.querySelector("video");
    const bar = item.querySelector(".reel-progress");
    const fill = item.querySelector(".reel-progress-fill");
    const timeEl = item.querySelector(".reel-time");
    if (!video || !bar || !fill) return;

    const updateTime = () => {
      if (timeEl) {
        timeEl.textContent = formatTime(video.currentTime);
      }
    };

    video.addEventListener("loadedmetadata", updateTime);
    video.addEventListener("timeupdate", () => {
      if (video.duration) {
        fill.style.width = (video.currentTime / video.duration) * 100 + "%";
      }
      updateTime();
    });
    updateTime();

    let dragging = false;
    const seek = (clientX) => {
      const rect = bar.getBoundingClientRect();
      let ratio = (clientX - rect.left) / rect.width;
      ratio = Math.min(Math.max(ratio, 0), 1);
      if (video.duration) {
        video.currentTime = ratio * video.duration;
        fill.style.width = ratio * 100 + "%";
        updateTime();
      }
    };

    bar.addEventListener("pointerdown", (e) => {
      e.stopPropagation();
      dragging = true;
      try {
        bar.setPointerCapture(e.pointerId);
      } catch (err) {}
      seek(e.clientX);
    });
    bar.addEventListener("pointermove", (e) => {
      if (dragging) seek(e.clientX);
    });
    bar.addEventListener("pointerup", () => (dragging = false));
    bar.addEventListener("pointercancel", () => (dragging = false));
  });
}

document.addEventListener("DOMContentLoaded", initReelSeek);

// إضافة مراقبة للتمرير
const reelsContainer = document.getElementById("reelsContainer");
let scrollTimeout;

if (reelsContainer) {
  reelsContainer.addEventListener("scroll", function () {
    clearTimeout(scrollTimeout);
    scrollTimeout = setTimeout(() => {
      const reelItems = document.querySelectorAll(".reel-item");
      const containerRect = reelsContainer.getBoundingClientRect();

      reelItems.forEach((item) => {
        const itemRect = item.getBoundingClientRect();
        const video = item.querySelector("video");
        const playOverlay = item.querySelector(".play-overlay");
        const isVisible =
          itemRect.top >= containerRect.top &&
          itemRect.bottom <= containerRect.bottom;

        if (!isVisible && !video.paused) {
          video.pause();
          if (playOverlay) {
            playOverlay.style.opacity = "1";
            playOverlay.style.pointerEvents = "all";
          }
          item.classList.remove("playing");
        }
      });
    }, 100);
  });
}

const video = document.querySelector(".my-video");
const progress = document.querySelector(".progress");

if (video && progress) {
  video.addEventListener("timeupdate", () => {
    const percent = (video.currentTime / video.duration) * 100;
    progress.style.width = percent + "%";
  });
}

const progressBar = document.querySelector(".progress-bar");

if (progressBar && video) {
  progressBar.addEventListener("click", (e) => {
    const rect = progressBar.getBoundingClientRect();
    const clickX = e.clientX - rect.left;
    const width = rect.width;
    const newTime = (clickX / width) * video.duration;
    video.currentTime = newTime;
  });
}

function scrollToTop() {
  window.scrollTo({ top: 0, behavior: "smooth" });
}

document.addEventListener("DOMContentLoaded", function () {
  const path = (
    window.location.pathname.split("/").pop() || "index.html"
  ).toLowerCase();
  const navLinks = document.querySelectorAll(".navbar-nav .nav-link");
  let matched = false;

  navLinks.forEach((link) => {
    const href = (link.getAttribute("href") || "")
      .split("/")
      .pop()
      .toLowerCase();
    link.classList.remove("active");
    if (href && href !== "#" && href === path) {
      link.classList.add("active");
      matched = true;
    }
  });

  if (!matched && (path === "" || path === "index.html")) {
    const home = document.querySelector(
      '.navbar-nav .nav-link[data-i18n="nav_home"]',
    );
    if (home) home.classList.add("active");
  }
  if (!matched && (path === "" || path === "content.html")) {
    const contact = document.querySelector(
      '.navbar-nav .nav-link[data-i18n="nav_content"]',
    );
    if (contact) contact.classList.add("active");
  }
});

document.addEventListener("DOMContentLoaded", function () {
  const navCollapse = document.getElementById("mainNav");
  const navToggler = document.querySelector(
    '.navbar-toggler[data-bs-target="#mainNav"]',
  );
  if (!navCollapse || !navToggler) return;

  function closeNav() {
    if (!navCollapse.classList.contains("show")) return;
    if (window.bootstrap && bootstrap.Collapse) {
      bootstrap.Collapse.getOrCreateInstance(navCollapse).hide();
    } else {
      navCollapse.classList.remove("show");
    }
  }

  document.addEventListener("click", function (e) {
    if (!navCollapse.classList.contains("show")) return;
    if (navCollapse.contains(e.target) || navToggler.contains(e.target)) return;
    closeNav();
  });

  navCollapse.querySelectorAll(".nav-link").forEach((link) => {
    link.addEventListener("click", closeNav);
  });
});

document.addEventListener("DOMContentLoaded", function () {
  const items = document.querySelectorAll(".timeline-item[data-story]");
  const paragraphs = document.querySelectorAll(".story-content .story-text");
  if (!items.length || paragraphs.length < 2) return;

  function selectStory(story) {
    items.forEach((el) => {
      el.classList.toggle("active", el.dataset.story === story);
    });
    paragraphs[0].setAttribute("data-i18n-html", `story_paragraph_${story}_1`);
    paragraphs[1].setAttribute("data-i18n-html", `story_paragraph_${story}_2`);

    if (typeof applyTranslations === "function") {
      const lang =
        typeof getCurrentLang === "function"
          ? getCurrentLang()
          : localStorage.getItem("lang") || "ar";
      applyTranslations(lang);
    }
  }

  const storyBox = document.querySelector(".story-box");

  items.forEach((el) => {
    el.addEventListener("click", function (e) {
      e.preventDefault();
      const story = el.dataset.story;
      if (story) selectStory(story);
      if (storyBox) {
        storyBox.scrollIntoView({ behavior: "smooth", block: "center" });
      }
    });
  });

  const initial = document.querySelector(".timeline-item.active[data-story]");
  selectStory(initial ? initial.dataset.story : "journey");
});

// Pin the top contact bar (nav-face) and the navbar to the top while scrolling
document.addEventListener("DOMContentLoaded", function () {
  // guard against double-init (some pages include script.js twice)
  if (document.querySelector(".header-bar")) return;

  const navFace = document.querySelector("header .nav-face");
  const navbar = document.querySelector("header .navbar");
  if (!navbar) return;

  // Wrap nav-face + navbar so they pin together as one bar while keeping
  // their own centered layout (and the hero's negative-margin overlap).
  const bar = document.createElement("div");
  bar.className = "header-bar";
  const firstNode = navFace || navbar;
  firstNode.parentNode.insertBefore(bar, firstNode);
  if (navFace) bar.appendChild(navFace);
  bar.appendChild(navbar);

  // Spacer preserves the bar's height in the flow once it goes fixed
  const spacer = document.createElement("div");
  let triggerPoint = bar.offsetTop;

  function onScroll() {
    const shouldFix = window.scrollY > triggerPoint;
    if (shouldFix && !bar.classList.contains("is-fixed")) {
      spacer.style.height = bar.offsetHeight + "px";
      bar.parentNode.insertBefore(spacer, bar);
      bar.classList.add("is-fixed");
    } else if (!shouldFix && bar.classList.contains("is-fixed")) {
      bar.classList.remove("is-fixed");
      if (spacer.parentNode) spacer.parentNode.removeChild(spacer);
    }
  }

  window.addEventListener("scroll", onScroll, { passive: true });
  window.addEventListener("resize", function () {
    if (!bar.classList.contains("is-fixed")) triggerPoint = bar.offsetTop;
  });
  onScroll();
});

/* ============================================================
   تعليقات كل ريل — تتغيّر تلقائياً حسب الريل المعروض عند التمرير
   ============================================================ */
(function () {
  const map = window.reelComments || {};
  const counts = window.reelCommentCounts || {};
  const palette = ["av-green", "av-orange", "av-blue", "av-gray"];

  function relTime(ts) {
    if (!ts) return "";
    const d = new Date(String(ts).replace("+0000", "Z"));
    if (isNaN(d.getTime())) return "";
    const s = Math.floor((Date.now() - d.getTime()) / 1000);
    if (s < 60) return "الآن";
    const m = Math.floor(s / 60);
    if (m < 60) return "منذ " + m + " دقيقة";
    const h = Math.floor(m / 60);
    if (h < 24) return "منذ " + h + " ساعة";
    const dd = Math.floor(h / 24);
    if (dd < 30) return "منذ " + dd + " يوم";
    return d.toLocaleDateString("ar");
  }

  function toComment(c, i) {
    const name = (c.name || "مستخدم").trim();
    return {
      id: 100000 + i,
      av: palette[i % palette.length],
      letter: name.charAt(0) || "؟",
      name: name,
      text: c.text || "",
      time: relTime(c.time),
      likes: c.likes || 0,
      liked: false,
      replies: [],
    };
  }

  // متاحة عالمياً — تُحمّل تعليقات ريل معيّن في لوحة التعليقات
  window.loadReelComments = function (reelId) {
    if (typeof commentsData === "undefined") return;
    const items = (map[reelId] || []).map(toComment);
    commentsData = items; // إعادة تعيين المصدر العام للتعليقات
    const counter = document.querySelector(".comments-count");
    if (counter) {
      const n = counts[reelId] != null ? counts[reelId] : items.length;
      const word = typeof t === "function" ? t("comments_word") : "التعليقات";
      counter.textContent = word + " (" + n + ")";
    }
    if (typeof renderComments === "function") renderComments();
  };

  document.addEventListener("DOMContentLoaded", function () {
    const container = document.getElementById("reelsContainer");
    if (!container) return;
    const items = Array.from(container.querySelectorAll(".reel-item[data-reel-id]"));
    if (!items.length) return;

    // ابدأ بتعليقات أول ريل
    let activeId = items[0].dataset.reelId;
    window.loadReelComments(activeId);

    // راقب أي ريل هو الظاهر داخل الحاوية عند التمرير
    const io = new IntersectionObserver(
      function (entries) {
        let best = null;
        let bestRatio = 0;
        entries.forEach(function (e) {
          if (e.isIntersecting && e.intersectionRatio > bestRatio) {
            bestRatio = e.intersectionRatio;
            best = e.target;
          }
        });
        if (best && best.dataset.reelId && best.dataset.reelId !== activeId) {
          activeId = best.dataset.reelId;
          window.loadReelComments(activeId);
        }
      },
      { root: container, threshold: [0.4, 0.6, 0.8] }
    );
    items.forEach(function (it) {
      io.observe(it);
    });
  });
})();
