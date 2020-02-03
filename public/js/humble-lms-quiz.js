/**
 * This module creates and returns an evaluation object for a quiz.
 * 
 * @since   0.0.1
 */
var Humble_LMS_Quiz

(function ($) {
  Humble_LMS_Quiz = {
    evaluate: function (quizIds) {
      let quizzes = $('.humble-lms-quiz-single')
      let passing_grades = [];
        quizzes.each(function (index, quiz) { passing_grades.push(parseInt($(quiz).data('passing-grade'))) })
      
      let passing_required = false
        quizzes.each(function (index, quiz) { passing_required = parseInt($(quiz).data('passing-required')) === 1 })
      
      let passing_grade = Math.round(passing_grades.reduce(function (total, number ) { return total + number }) / passing_grades.length)
      let questions_multiple_choice = $('.humble-lms-quiz-question.multiple_choice, .humble-lms-quiz-question.single_choice')
      let answers = $('.humble-lms-answer')
      let evaluation = {
        quizIds: quizIds,
        questions: questions_multiple_choice.length,
        answers: answers.length,
        correct: 0,
        incorrect: 0,
        score: 0,
        grade: 0,
        passing_grade: passing_grade,
        passing_required: passing_required,
        completed: false
      }

      questions_multiple_choice.each(function (index, question) {
        answers = $(question).find('.humble-lms-answer')
        answers.each(function (index, answer) {
          input = $(answer).find('input')
          if ($(input).val() == 1) {
            evaluation.correct++
            if ($(input).is(':checked')) {
              evaluation.score++
            }
          } else {
            evaluation.incorrect++
            if ($(input).is(':checked')) {
              evaluation.score--
            }
          }
        })
      })

      evaluation.grade = Math.round(evaluation.score / evaluation.correct * 100, 2)
      evaluation.completed = evaluation.grade >= evaluation.passing_grade ? true : false

      return evaluation
    }
  }
})(jQuery)