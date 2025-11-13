import type { Event, League, LeagueTextSection } from "../types";

// Shared league descriptions for reuse across pages
export const leagueDescriptions: Record<
  "day" | "evening" | "stick" | "green",
  LeagueTextSection
> = {
  day: {
    intro: [
      "Every Monday through Thursday mornings from 9:30-11:30 is “Mixed Drop-In”, where new teams are made up each day. This is a great way to meet new people and enjoy a fun game of curling.",
    ],
  },
  evening: {
    intro: [
      "Our evening leagues are a great way to unwind after a long day and enjoy some friendly competition. We offer leagues for all skill levels, from beginner to competitive. Whether you’re looking to join a team or just want to drop in for a game, we have a league for you.",
    ],
  },
  stick: {
    // Keep index to an intro only; full details live on the stick page
    intro: [
      "Every Thursday at 1:00 PM through 3:30 PM is our social / sign up day done through email. Teams are made up randomly to promote the social aspect so everyone has a chance to get to know each other. This is intended for all levels of curlers including beginners. Curlers can choose to do only 1 of the 2 games if they want.",
      "There is a 15 minute break between the 2 games, allowing for snack, coffee, and socializing.",
      "Every Monday, Wednesday, and Friday at 1:00 PM through 3:30 PM is scheduled team play. As curlers learn and develop skills they tend to gravitate toward these teams where players have regular partners. These teams / leagues are slightly more competitive but are still very social and fun.",
    ],
    details: [
      "Stick curling is a variation of the sport that allows players to deliver stones while standing upright. This makes it an accessible and inclusive option for individuals with mobility issues or physical limitations.",
      "In addition to regular league play, our curlers engage in friendly matches against stick curlers from other South Shore curling clubs, fostering a spirit of camaraderie and competition within the local curling community.",
      "Our stick curling program accommodates all types of players: Drop-In for casual players to join anytime, and Team Play for those looking to compete in a more structured setting.",
      "Each day features two games, offering plenty of opportunities for both new and experienced players to participate.",
    ],
  },
//   green: {
//     intro: [
//       "Our Green League is perfect for new curlers looking to learn the game in a fun and supportive environment. This league is open to anyone who has completed our Learn to Curl program.",
//       "This is a drop-in league, each week we will form new teams, giving everyone a chance to play with different people and learn new skills. The league runs every Sunday from 6:00-8:00 PM.",
//     ],
//   },
};

// Unified league schedule data used across pages
export const leagues: League[] = [
  // Daytime
  {
    day: "Monday",
    type: "day",
    events: [
      {
        name: "Mixed Drop-In",
        times: ["9:30 - 11:30 AM"],
        competitionLevel: "casual",
        color: "bg-blue-50 text-blue-900",
      },
    ],
  },
  {
    day: "Tuesday",
    type: "day",
    events: [
      {
        name: "Mixed Drop-In",
        times: ["9:30 - 11:30 AM"],
        competitionLevel: "casual",
        color: "bg-blue-50 text-blue-900",
      },
    ],
  },
  {
    day: "Wednesday",
    type: "day",
    events: [
      {
        name: "Mixed Drop-In",
        times: ["9:30 - 11:30 AM"],
        competitionLevel: "casual",
        color: "bg-blue-50 text-blue-900",
      },
    ],
  },
  {
    day: "Thursday",
    type: "day",
    events: [
      {
        name: "Mixed Drop-In",
        times: ["9:30 - 11:30 AM"],
        competitionLevel: "casual",
        color: "bg-green-50 text-green-900",
      },
    ],
  },
  { day: "Friday", type: "day", events: [] },

  // Evening
  {
    day: "Monday",
    type: "evening",
    events: [
      {
        name: "Drop-In Curling",
        times: ["6:30-8:00 PM"],
        competitionLevel: "casual",
        color: "bg-indigo-50 text-indigo-900",
        ends: 6,
      },
    ],
  },
  {
    day: "Tuesday",
    type: "evening",
    events: [
      {
        name: "Competitive ",
        times: ["6:30 PM"],
        competitionLevel: "high",
        ends: 8,
        color: "bg-red-50 text-red-900",
      },
    ],
  },
  {
    day: "Wednesday",
    type: "evening",
    events: [
      {
        name: "Social ",
        times: ["6:30 PM", "8:15 PM"],
        competitionLevel: "social",
        ends: 6,
        color: "bg-green-50 text-green-900",
      },
    ],
  },
  {
    day: "Thursday",
    type: "evening",
    events: [
      {
        name: "Competitive ",
        times: ["6:30 PM", "8:30 PM"],
        competitionLevel: "medium",
        ends: 8,
        color: "bg-yellow-50 text-yellow-900",
      },
    ],
  },
  {
    day: "Friday",
    type: "evening",
    events: [
      {
        name: "Social ",
        times: ["6:30 PM", "8:15 PM"],
        competitionLevel: "social",
        ends: 6,
        color: "bg-green-50 text-green-900",
      },
    ],
  },

  // Stick
  {
    day: "Monday",
    type: "stick",
    events: [
      {
        name: "Team / League",
        times: ["1:00 PM - 3:30 PM"],
        competitionLevel: "social",
        ends: 6,
        color: "bg-green-50 text-green-900",
      },
    ],
  },
  {
    day: "Wednesday",
    type: "stick",
    events: [
      {
        name: "Team / League",
        times: ["1:00 PM - 3:30 PM"],
        competitionLevel: "social",
        ends: 6,
        color: "bg-green-50 text-green-900",
      },
    ],
  },
  {
    day: "Thursday",
    type: "stick",
    events: [
      {
        name: "Social / Sign Up",
        times: ["1:00 PM - 3:30 PM"],
        competitionLevel: "social",
        ends: 6,
        color: "bg-green-50 text-green-900",
      },
    ],
  },
  {
    day: "Friday",
    type: "stick",
    events: [
      {
        name: "Team / League",
        times: ["1:00 PM - 3:30 PM"],
        competitionLevel: "beginner",
        ends: 6,
        color: "bg-green-50 text-green-900",
      },
    ],
  },
];

export const greenLeague: League = {
  day: "Monday",
  type: "day",
  events: [
    {
      name: "Drop-in Curling",
      times: ["7:35 PM - 9:00 PM"],
      competitionLevel: "beginner",
      color: "bg-indigo-50 text-indigo-900",
    },
  ],
};



