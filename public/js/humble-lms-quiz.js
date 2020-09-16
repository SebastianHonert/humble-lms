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
        let question_score = 0
        let question_correct = 0
        let question_incorrect = 0

        answers = $(question).find('.humble-lms-answer')
        answers.each(function (index, answer) {
          input = $(answer).find('input')
          if ($(input).val() == 1) {
            question_correct++
            if ($(input).is(':checked')) {
              question_score++
            }
          } else {
            question_incorrect++
            if ($(input).is(':checked')) {
              question_score--
            }
          }
        })

        evaluation.score += question_score
        evaluation.correct += question_incorrect
        evaluation.incorrect += question_incorrect
      })

      if (evaluation.score < 0) {
        evaluation.score = 0
      }

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