import firebaseConfig from "./firebase-config.json";

firebase.initializeApp(firebaseConfig);

const db = firebase.firestore();
const categoryAnalyticsRef = db.collection("category_analytics");
const cityAnalyticsRef = db.collection("city_analytics");

categoryAnalyticsRef.onSnapshot((categorySnapshot) => {
  categorySnapshot.forEach((doc) => {
    const countContainer = document.getElementById(`category-${doc.id}`);

    countContainer.innerText = doc.data().count;
  });
});

cityAnalyticsRef.onSnapshot((citySnapshot) => {
  citySnapshot.forEach((doc) => {
    const countContainer = document.getElementById(`city-${doc.id}`);

    countContainer.innerText = doc.data().count;
  });
});
