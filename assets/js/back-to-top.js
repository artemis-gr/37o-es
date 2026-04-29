document.addEventListener("DOMContentLoaded", function () {
  console.log("back-to-top loaded");
  const btn = document.querySelector(".back-to-top-container");
  if (!btn) return;

  btn.addEventListener("click", function (e) {
    e.preventDefault();

    window.scrollTo({
      top: 0,
      left: 0,
      behavior: "smooth",
    });

    document.documentElement.scrollTo({
      top: 0,
      left: 0,
      behavior: "smooth",
    });

    document.body.scrollTo({
      top: 0,
      left: 0,
      behavior: "smooth",
    });
  });
});