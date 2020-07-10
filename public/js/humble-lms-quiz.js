/**
 * This module creates and returns an evaluation object for a quiz.
 * 
 * @since   0.0.1
 */
var Humble_LMS_Quiz

(function ($) {
  Humble_LMS_Quiz = {
    evaluate: function (quizIds) {
      let tryAgain = parseInt($('input[name="try-again"]').val())
      let quizzes = $('.humble-lms-quiz-single')
      let passing_percentages = [];
        quizzes.each(function (index, quiz) { passing_percentages.push(parseInt($(quiz).data('passing-percent'))) })
      
      let passing_required = false
        quizzes.each(function (index, quiz) { passing_required = parseInt($(quiz).data('passing-required')) === 1 })
      
      let passing_percent = Math.round(passing_percentages.reduce(function (total, number ) { return total + number }) / passing_percentages.length)
      let questions_multiple_choice = $('.humble-lms-quiz-question.multiple_choice, .humble-lms-quiz-question.single_choice')
      let answers = $('.humble-lms-answer')
      let evaluation = {
        quizIds: quizIds,
        tryAgain: tryAgain,
        questions: questions_multiple_choice.length,
        answers: answers.length,
        correct: 0,
        incorrect: 0,
        score: 0,
        percent: 0,
        passing_percent: passing_percent,
        passing_required: passing_required,
        completed: 0
      }

      if (tryAgain === 1) {
        return evaluation
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

      evaluation.percent = Math.round(evaluation.score / evaluation.correct * 100, 2)

      if (evaluation.percent < 0) {
        evaluation.percent = 0;
      } else if (evaluation.percent > 100) {
        evaluation.percent = 100;
      }

      evaluation.completed = evaluation.percent >= evaluation.passing_percent ? 1 : 0

      return evaluation
    }
  }
})(jQuery)