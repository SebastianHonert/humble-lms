var Humble_LMS_Quiz

(function ($) {
  Humble_LMS_Quiz = {
    evaluate: function () {
      let questions_multiple_choice = $('.humble-lms-quiz-question.multiple_choice, .humble-lms-quiz-question.single_choice')

      let evaluation = {
        correct: 0,
        incorrect: 0,
        score: 0,
        completed: false
      }

      questions_multiple_choice.each(function (index, question) {
        let answers = $(question).find('.humble-lms-answer')

        answers.each(function (index, answer) {
          input = $(answer).find('input')
          if ($(input).val() == 1) {
            evaluation.correct++

            if ($(input).is(':checked')) {
              evaluation.score++
            }
          } else {
            evaluation.incorrect++
          }
        })
      })

      evaluation.completed = evaluation.score === evaluation.correct ? true : false

      return evaluation
    }
  }
})(jQuery)